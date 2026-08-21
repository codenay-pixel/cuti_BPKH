<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'digits_between:8,18', Rule::unique('users', 'nip')->ignore($userId)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:pegawai,atasan_langsung,atasan,admin'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'tmt_pns' => ['nullable', 'date'],
            'no_telp' => ['nullable', 'string', 'max:30'],
            'atasan_id' => ['nullable', 'exists:users,id'],
            'tanda_tangan' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'hapus_tanda_tangan' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nip')) {
            $this->merge(['nip' => preg_replace('/[^0-9]/', '', (string) $this->input('nip'))]);
        }
    }

    public function messages(): array
    {
        return [
            'nip.digits_between' => 'NIP harus berupa angka, 8 sampai 18 digit. NIP PNS umumnya 18 digit.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'tanda_tangan.image' => 'Berkas tanda tangan harus berupa gambar (PNG atau JPG).',
            'tanda_tangan.max' => 'Ukuran gambar tanda tangan maksimal 2 MB.',
        ];
    }
}
