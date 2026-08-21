<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveService $leaveService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = LeaveRequest::where('user_id', $user->id)
            ->with(['leaveType', 'approvals.approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('leave_type_id', $request->jenis);
        }

        $riwayat = $query->paginate(10)->withQueryString();

        $this->leaveService->pastikanSaldoTahunan($user);
        $saldo = $this->leaveService->rincianSaldoTahunan($user);
        $leaveTypes = LeaveType::urut()->get();

        return view('pegawai.leave.index', compact('riwayat', 'saldo', 'leaveTypes'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $this->leaveService->pastikanSaldoTahunan($user);

        $leaveTypes = LeaveType::urut()->get();
        $saldo = $this->leaveService->rincianSaldoTahunan($user);
        $tertahan = $this->leaveService->hariTertahan($user);

        return view('pegawai.leave.create', compact('leaveTypes', 'saldo', 'tertahan'));
    }

    public function store(StoreLeaveRequest $request)
    {
        try {
            $this->leaveService->ajukanCuti(
                $request->validated(),
                $request->user(),
                $request->file('lampiran')
            );

            return redirect()->route('leave.index')
                ->with('success', 'Pengajuan cuti berhasil dikirim ke atasan langsung Anda.');
        } catch (\Exception $e) {
            return back()->withErrors(['saldo' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, LeaveRequest $leaveRequest)
    {
        $this->pastikanMilikSendiri($leaveRequest);
        $leaveRequest->load('approvals');

        abort_unless(
            $leaveRequest->bolehDiubah(),
            403,
            'Pengajuan yang sudah diputuskan atasan tidak dapat diubah lagi.'
        );

        $user = $request->user();
        $this->leaveService->pastikanSaldoTahunan($user);

        $leaveTypes = LeaveType::urut()->get();
        $saldo = $this->leaveService->rincianSaldoTahunan($user);
        $tertahan = $this->leaveService->hariTertahan($user, null, $leaveRequest->id);

        return view('pegawai.leave.edit', compact('leaveRequest', 'leaveTypes', 'saldo', 'tertahan'));
    }

    public function update(StoreLeaveRequest $request, LeaveRequest $leaveRequest)
    {
        $this->pastikanMilikSendiri($leaveRequest);
        $leaveRequest->load('approvals');

        abort_unless(
            $leaveRequest->bolehDiubah(),
            403,
            'Pengajuan yang sudah diputuskan atasan tidak dapat diubah lagi.'
        );

        try {
            $this->leaveService->perbaruiCuti(
                $leaveRequest,
                $request->validated(),
                $request->file('lampiran')
            );

            return redirect()->route('leave.show', $leaveRequest)
                ->with('success', 'Perubahan pengajuan cuti berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withErrors(['saldo' => $e->getMessage()])->withInput();
        }
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->pastikanMilikSendiri($leaveRequest);

        $leaveRequest->load(['leaveType', 'user.atasan', 'approvals.approver']);

        return view('pegawai.leave.show', compact('leaveRequest'));
    }

    /** Cetak Formulir Permintaan dan Pemberian Cuti sebagai PDF. */
    public function cetak(Request $request, LeaveRequest $leaveRequest)
    {
        // Pemohon, para penyetuju, dan admin boleh mencetak.
        $user = $request->user();
        $boleh = $leaveRequest->user_id === $user->id
            || $user->isAdmin()
            || $user->isKepalaBalai()
            || $leaveRequest->current_approver_id === $user->id
            || $leaveRequest->approvals()->where('approver_id', $user->id)->exists();

        abort_unless($boleh, 403, 'Anda tidak berhak mencetak formulir ini.');

        // Kunci utama: formulir baru boleh keluar setelah disetujui penuh.
        // Dicek di sini, bukan sekadar menyembunyikan tombol, supaya tidak bisa
        // ditembus dengan mengetik alamat URL-nya langsung.
        abort_unless(
            $leaveRequest->sudahDisetujuiPenuh(),
            403,
            'Formulir cuti baru dapat dicetak setelah disetujui oleh atasan langsung '
            . 'dan pejabat pemberi cuti.'
        );

        $leaveRequest->load(['leaveType', 'user.atasan.atasan', 'approvals.approver']);

        $saldo = $this->leaveService->rincianSaldoTahunan($leaveRequest->user);

        // Nama pejabat dicetak walau keputusannya belum ada, supaya formulir bisa
        // dibawa untuk ditandatangani. Kotak centang tetap kosong sampai benar-benar
        // ada keputusan di sistem.
        $apAtasan  = $leaveRequest->approvalAtasanLangsung();
        $apPejabat = $leaveRequest->approvalKepalaBalai();

        $penyetuju = [
            'atasan'  => $apAtasan?->approver ?? $leaveRequest->user->atasan,
            'pejabat' => $apPejabat?->approver
                ?? $leaveRequest->user->atasan?->atasan
                ?? User::where('role', 'atasan')->first(),
        ];

        $pdf = Pdf::loadView('pegawai.leave.cetak', compact('leaveRequest', 'saldo', 'penyetuju'))
            ->setPaper('a4', 'portrait');

        $namaFile = 'Formulir-Cuti-' . str_replace(' ', '-', $leaveRequest->user->name)
            . '-' . $leaveRequest->tanggal_mulai->format('Ymd') . '.pdf';

        return $request->boolean('unduh')
            ? $pdf->download($namaFile)
            : $pdf->stream($namaFile);
    }

    /** Batalkan pengajuan yang masih menunggu persetujuan atasan langsung. */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->pastikanMilikSendiri($leaveRequest);

        $leaveRequest->load('approvals');

        abort_unless(
            $leaveRequest->bolehDiubah(),
            403,
            'Pengajuan yang sudah diputuskan atasan tidak dapat dibatalkan.'
        );

        if ($leaveRequest->lampiran) {
            Storage::disk('public')->delete($leaveRequest->lampiran);
        }

        $leaveRequest->delete();

        return redirect()->route('leave.index')->with('success', 'Pengajuan cuti dibatalkan.');
    }

    private function pastikanMilikSendiri(LeaveRequest $leaveRequest): void
    {
        abort_if(
            $leaveRequest->user_id !== auth()->id() && ! auth()->user()->isAdmin(),
            403,
            'Anda hanya dapat melihat pengajuan cuti Anda sendiri.'
        );
    }
}
