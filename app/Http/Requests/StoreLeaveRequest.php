<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
    return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'min:10'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

        public function messages(): array
    {
        return [
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.min' => 'Alasan cuti minimal 10 karakter.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.max' => 'Ukuran lampiran maksimal 2MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mulai = $this->input('tanggal_mulai');
            $selesai = $this->input('tanggal_selesai');

            if (!$mulai || !$selesai) {
                return;
            }

            $overlap = LeaveRequest::where('user_id', $this->user()->id)
                ->whereIn('status', ['menunggu', 'disetujui_atasan', 'disetujui'])
                ->where('tanggal_mulai', '<=', $selesai)
                ->where('tanggal_selesai', '>=', $mulai)
                ->exists();

            if ($overlap) {
                $validator->errors()->add('tanggal_mulai', 'Anda sudah memiliki pengajuan cuti pada rentang tanggal ini.');
            }
        });
    }
}