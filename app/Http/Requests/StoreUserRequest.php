<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'nip' => ['required', 'string', 'unique:users,nip'],
            'role' => ['required', 'in:pegawai,atasan_langsung,atasan,admin'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'atasan_id' => ['nullable', 'exists:users,id'],
        ];
    }
}