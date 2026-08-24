<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the teacher login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('teacher')->check()) {
            return redirect()->route('teacher.attendance');
        }

        return view('teacher.auth.login');
    }

    /**
     * Handle teacher email identification.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:teachers,email',
        ], [
            'email.exists' => 'Email yang Anda masukkan tidak terdaftar sebagai tenaga pendidik.',
        ]);

        $teacher = Teacher::where('email', $request->email)->first();

        // Store temporary teacher ID in session for confirmation step
        $request->session()->put('pending_teacher_id', $teacher->id);

        return redirect()->route('teacher.confirm');
    }

    /**
     * Show the profile confirmation screen.
     */
    public function showConfirmForm(Request $request)
    {
        if (Auth::guard('teacher')->check()) {
            return redirect()->route('teacher.attendance');
        }

        $teacherId = $request->session()->get('pending_teacher_id');

        if (!$teacherId) {
            return redirect()->route('teacher.login')->with('error', 'Silakan masukkan email Anda terlebih dahulu.');
        }

        $teacher = Teacher::findOrFail($teacherId);

        return view('teacher.auth.confirm', compact('teacher'));
    }

    /**
     * Confirm profile and finalize login.
     */
    public function confirm(Request $request)
    {
        $teacherId = $request->session()->get('pending_teacher_id');

        if (!$teacherId) {
            return redirect()->route('teacher.login')->with('error', 'Sesi Anda telah kedaluwarsa.');
        }

        $teacher = Teacher::findOrFail($teacherId);

        // Perform login
        Auth::guard('teacher')->login($teacher);

        // Clean up pending session
        $request->session()->forget('pending_teacher_id');

        return redirect()->intended(route('teacher.attendance'))->with('success', 'Berhasil masuk ke portal presensi.');
    }

    /**
     * Log the teacher out.
     */
    public function logout(Request $request)
    {
        Auth::guard('teacher')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal')->with('success', 'Anda telah berhasil keluar.');
    }
}
