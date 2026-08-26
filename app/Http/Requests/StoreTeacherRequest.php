<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth validation will be done by Policy in Controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'nip' => 'nullable|string|max:50|unique:teachers,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:teachers,email',
            'password' => 'required|string|min:6', // PIN/Password
            'position' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
            'status' => 'required|string|in:active,inactive',
        ];

        if (auth()->check() && auth()->user()->hasRole('superadmin')) {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah terdaftar sebagai tenaga pendidik.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'avatar.max' => 'Ukuran foto profil tidak boleh melebihi 2MB.',
            'avatar.image' => 'Berkas harus berupa gambar.',
        ];
    }
}
