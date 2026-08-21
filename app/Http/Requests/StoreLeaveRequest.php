<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'leave_type_id'   => ['required', 'exists:leave_types,id'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan'          => ['required', 'string', 'min:10', 'max:1000'],
            'alamat_cuti'     => ['required', 'string', 'max:255'],
            'telepon_cuti'    => ['required', 'string', 'max:30'],
            'lampiran'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required'         => 'Silakan pilih jenis cuti.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.min'                     => 'Alasan cuti minimal 10 karakter.',
            'alamat_cuti.required'           => 'Alamat selama menjalankan cuti wajib diisi (dicantumkan di formulir).',
            'telepon_cuti.required'          => 'Nomor telepon yang dapat dihubungi wajib diisi.',
            'lampiran.mimes'                 => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.max'                   => 'Ukuran lampiran maksimal 2 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->cekLampiranWajib($validator);
            $this->cekTanggalMundur($validator);
            $this->cekTumpangTindih($validator);
        });
    }

    /** Jenis cuti tertentu wajib menyertakan dokumen pendukung. */
    private function cekLampiranWajib(Validator $validator): void
    {
        $jenis = LeaveType::find($this->input('leave_type_id'));

        // Saat mengubah, berkas lama tetap dihitung sebagai lampiran yang sah.
        if ($this->route('leaveRequest')?->lampiran) {
            return;
        }

        if ($jenis && $jenis->perlu_lampiran && ! $this->hasFile('lampiran')) {
            $validator->errors()->add(
                'lampiran',
                $jenis->nama_cuti . ' wajib melampirkan dokumen pendukung. Lihat daftar syarat di atas.'
            );
        }
    }

    /**
     * Cuti sakit dan cuti karena alasan penting boleh diajukan mundur
     * (kejadiannya sudah lewat). Jenis lain harus untuk tanggal ke depan.
     */
    private function cekTanggalMundur(Validator $validator): void
    {
        $jenis = LeaveType::find($this->input('leave_type_id'));
        $mulai = $this->input('tanggal_mulai');

        if (! $jenis || ! $mulai) {
            return;
        }

        $bolehMundur = in_array($jenis->kode, [LeaveType::SAKIT, LeaveType::ALASAN_PENTING], true);

        if (! $bolehMundur && $mulai < now()->toDateString()) {
            $validator->errors()->add('tanggal_mulai', 'Tanggal mulai tidak boleh sebelum hari ini.');
        }
    }

    private function cekTumpangTindih(Validator $validator): void
    {
        $mulai = $this->input('tanggal_mulai');
        $selesai = $this->input('tanggal_selesai');

        if (! $mulai || ! $selesai) {
            return;
        }

        // Saat mengubah, pengajuan itu sendiri jangan dianggap bentrok.
        $sedangDiubah = $this->route('leaveRequest')?->id;

        $overlap = LeaveRequest::where('user_id', $this->user()->id)
            ->whereIn('status', ['menunggu', 'disetujui_atasan', 'disetujui'])
            ->where('tanggal_mulai', '<=', $selesai)
            ->where('tanggal_selesai', '>=', $mulai)
            ->when($sedangDiubah, fn ($q) => $q->where('id', '!=', $sedangDiubah))
            ->exists();

        if ($overlap) {
            $validator->errors()->add('tanggal_mulai', 'Anda sudah memiliki pengajuan cuti pada rentang tanggal ini.');
        }
    }
}
