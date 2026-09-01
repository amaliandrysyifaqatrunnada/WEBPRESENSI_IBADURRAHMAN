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
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->hasRole('koordinator')) {
                return redirect()->route('coordinator.dashboard');
            }
        }

        $units = \App\Models\Unit::where('active', true)->orderBy('name', 'asc')->get();

        return view('teacher.auth.login', compact('units'));
    }

    /**
     * Handle teacher email identification.
     */
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama atau Email wajib diisi.',
        ]);

        $input = trim($request->name);

        // Check if input is email format
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail) {
            // Coordinators/Admins MUST use Email to log in
            $user = \App\Models\User::where('email', strtolower($input))->first();
            if ($user) {
                if ($user->hasRole('koordinator')) {
                    $request->session()->put('pending_coordinator_id', $user->id);
                    return redirect()->route('teacher.confirm');
                } else {
                    return redirect()->route('admin.login')
                        ->withInput(['name' => $input])
                        ->with('info', "Email '{$input}' terdaftar sebagai Admin. Silakan masuk di halaman ini untuk melanjutkan.");
                }
            }

            // Teachers can also use Email
            $teacher = Teacher::where('email', strtolower($input))->where('status', 'active')->first();
            if ($teacher) {
                $request->session()->put('pending_teacher_id', $teacher->id);
                return redirect()->route('teacher.confirm');
            }
        } else {
            // If it is a name, ONLY search in teachers table (coordinators cannot use names to log in)
            $teacher = Teacher::where(function($query) use ($input) {
                $query->where('name', 'like', $input)
                      ->orWhere('name', 'like', "%{$input}%");
            })->where('status', 'active')->first();

            if ($teacher) {
                $request->session()->put('pending_teacher_id', $teacher->id);
                return redirect()->route('teacher.confirm');
            }
        }

        return back()->withErrors([
            'name' => 'Nama atau Email yang Anda masukkan tidak terdaftar di sistem.',
        ])->withInput();
    }

    /**
     * Show the profile confirmation screen.
     */
    public function showConfirmForm(Request $request)
    {
        if (Auth::guard('teacher')->check()) {
            return redirect()->route('teacher.attendance');
        }
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->hasRole('koordinator')) {
                return redirect()->route('coordinator.dashboard');
            }
        }

        $teacherId = $request->session()->get('pending_teacher_id');
        $coordinatorId = $request->session()->get('pending_coordinator_id');

        if (!$teacherId && !$coordinatorId) {
            return redirect()->route('teacher.login')->with('error', 'Silakan masukkan nama Anda terlebih dahulu.');
        }

        if ($teacherId) {
            $teacher = Teacher::findOrFail($teacherId);
            $profile = (object)[
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'avatar' => $teacher->avatar,
                'position' => $teacher->position,
                'unit' => $teacher->unit,
                'is_coordinator' => false
            ];
        } else {
            $coordinator = \App\Models\User::findOrFail($coordinatorId);
            $profile = (object)[
                'id' => $coordinator->id,
                'name' => $coordinator->name,
                'nip' => null,
                'avatar' => $coordinator->avatar,
                'position' => 'Koordinator ' . ($coordinator->unit ? $coordinator->unit->name : ''),
                'unit' => $coordinator->unit,
                'is_coordinator' => true
            ];
        }

        return view('teacher.auth.confirm', ['teacher' => $profile]);
    }

    /**
     * Confirm profile and finalize login.
     */
    public function confirm(Request $request)
    {
        $teacherId = $request->session()->get('pending_teacher_id');
        $coordinatorId = $request->session()->get('pending_coordinator_id');

        if (!$teacherId && !$coordinatorId) {
            return redirect()->route('teacher.login')->with('error', 'Sesi Anda telah kedaluwarsa.');
        }

        if ($teacherId) {
            $teacher = Teacher::findOrFail($teacherId);
            Auth::guard('teacher')->login($teacher);
            $request->session()->forget('pending_teacher_id');
            return redirect()->intended(route('teacher.attendance'))->with('success', 'Berhasil masuk ke portal presensi.');
        } else {
            $coordinator = \App\Models\User::findOrFail($coordinatorId);
            Auth::guard('web')->login($coordinator);
            $request->session()->forget('pending_coordinator_id');
            return redirect()->intended(route('coordinator.dashboard'))->with('success', 'Berhasil masuk ke dashboard koordinator.');
        }
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
