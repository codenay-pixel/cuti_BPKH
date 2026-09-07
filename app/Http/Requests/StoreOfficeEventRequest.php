<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama_acara'      => ['required', 'string', 'max:150'],
            'jenis'           => ['required', 'in:dinas_luar,rapat,diklat,lainnya'],
            'jenis_lainnya'   => ['nullable', 'required_if:jenis,lainnya', 'string', 'max:100'],
            'nomor_spt'       => ['nullable', 'required_if:jenis,dinas_luar', 'string', 'max:50'],
            'pegawai_id'      => ['nullable', 'exists:users,id'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi'          => ['nullable', 'string', 'max:150'],
            'keterangan'      => ['nullable', 'string', 'max:1000'],
            'lampiran'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_acara.required'          => 'Nama acara wajib diisi.',
            'jenis_lainnya.required_if'    => 'Sebutkan jenis kegiatannya.',
            'nomor_spt.required_if'        => 'Nomor SPT wajib diisi untuk kegiatan Dinas Luar.',
            'pegawai_id.exists'            => 'Pegawai yang dipilih tidak ditemukan.',
            'tanggal_mulai.required'       => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'lampiran.mimes'               => 'Foto/scan surat dinas harus berformat PDF, JPG, atau PNG.',
            'lampiran.max'                 => 'Ukuran berkas maksimal 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return ['lampiran' => 'surat dinas'];
    }
}
