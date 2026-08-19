<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'nip' => ['required', 'string', Rule::unique('users', 'nip')->ignore($userId)],
            'role' => ['required', 'in:pegawai,atasan_langsung,atasan,admin'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'atasan_id' => ['nullable', 'exists:users,id'],
        ];
    }
}