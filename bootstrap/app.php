<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'admin/logout',
            'teacher/logout',
            'admin/login',
            'teacher/login',
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('teacher/*') || $request->is('teacher')) {
                return route('teacher.login');
            }
            return route('admin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('admin/login') || $request->is('admin/login/*')) {
                return redirect()->route('admin.login')->with('error', 'Sesi halaman login telah kedaluwarsa. Silakan masukkan ulang email dan kata sandi Anda.');
            }
            if ($request->is('teacher/login') || $request->is('teacher/login/*') || $request->is('teacher/confirm')) {
                return redirect()->route('teacher.login')->with('error', 'Sesi halaman login telah kedaluwarsa. Silakan masukkan email Anda.');
            }
            if ($request->is('admin/logout')) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('portal')->with('success', 'Anda telah berhasil keluar dari sistem.');
            }
            if ($request->is('teacher/logout')) {
                \Illuminate\Support\Facades\Auth::guard('teacher')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('portal')->with('success', 'Anda telah berhasil keluar dari sistem.');
            }
            return redirect()->back()->with('error', 'Halaman atau sesi Anda telah kedaluwarsa. Silakan coba kembali.');
        });
    })->create();
