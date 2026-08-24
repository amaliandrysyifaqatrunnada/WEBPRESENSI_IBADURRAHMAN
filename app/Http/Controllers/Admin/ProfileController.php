<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show admin profile edit page.
     */
    public function edit()
    {
        $user = Auth::user();
        $units = \App\Models\Unit::all();
        return view('admin.profile.edit', compact('user', 'units'));
    }

    /**
     * Update admin profile details (name, email, unit_id, avatar).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ];

        if (!$user->hasRole('superadmin')) {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $request->validate($rules, [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh admin lain.',
            'unit_id.required' => 'Unit sekolah wajib dipilih.',
            'unit_id.exists' => 'Unit sekolah tidak valid.',
            'avatar.image' => 'File avatar harus berupa gambar.',
            'avatar.max' => 'Ukuran avatar maksimal adalah 2MB.',
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        // unit_id is locked to prevent administrative unit switching from profile page

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Show edit password form view.
     */
    public function editPassword()
    {
        return view('admin.profile.password');
    }

    /**
     * Change admin password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Perform standard request validation
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.'
            ]);
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password baru tidak boleh sama dengan password lama.'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
