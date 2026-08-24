<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be verified in controller via Policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'action_type' => 'required|string|in:check_in,check_out',
            'status' => 'nullable|string|in:hadir,terlambat,izin,sakit,alpa',
            'date' => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action_type.in' => 'Tipe aksi absen harus berupa check_in atau check_out.',
            'status.in' => 'Status absensi tidak valid.',
        ];
    }
}
