<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeEventRequest;
use App\Models\OfficeEvent;
use Illuminate\Support\Facades\Storage;

class OfficeEventController extends Controller
{
    public function store(StoreOfficeEventRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('surat-dinas', 'public');
        }

        // Tata Usaha/Admin boleh mencatatkan acara atas nama pegawai lain
        // lewat field pegawai_id. Pegawai biasa (dan siapa pun tanpa
        // pegawai_id terisi) selalu mencatat untuk dirinya sendiri.
        $pegawaiId = $data['pegawai_id'] ?? null;

        $data['user_id'] = ($pegawaiId && $request->user()->bisaCatatUntukOrangLain())
            ? (int) $pegawaiId
            : $request->user()->id;

        $data['dicatat_oleh_id'] = $request->user()->id;

        unset($data['pegawai_id']);

        OfficeEvent::create($data);

        return redirect()
            ->route('calendar.index', [
                'bulan' => date('n', strtotime($data['tanggal_mulai'])),
                'tahun' => date('Y', strtotime($data['tanggal_mulai'])),
            ])
            ->with('success', 'Acara berhasil ditambahkan ke kalender kantor.');
    }

    public function destroy(OfficeEvent $officeEvent)
    {

        abort_unless(
            $officeEvent->user_id === auth()->id()
                || $officeEvent->dicatat_oleh_id === auth()->id()
                || auth()->user()->isAdmin(),
            403,
            'Anda hanya dapat menghapus acara yang Anda buat sendiri.'
        );

        if ($officeEvent->lampiran) {
            Storage::disk('public')->delete($officeEvent->lampiran);
        }

        $officeEvent->delete();

        return back()->with('success', 'Acara berhasil dihapus.');
    }
}
