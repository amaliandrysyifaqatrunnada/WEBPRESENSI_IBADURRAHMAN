<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Teacher\AuthController as TeacherAuthController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use Illuminate\Support\Facades\Route;

// 1. Portal Route
Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/qr-presensi', [PortalController::class, 'publicQr'])->name('qr.public');
Route::get('/qr-presensi/token', [PortalController::class, 'getPublicQrToken'])->name('qr.public.token');

// Face ID Public Routes (School Device Verification Portal)
Route::get('/face-id', [\App\Http\Controllers\FaceIdController::class, 'index'])->name('face.id.portal');
Route::post('/face-id/search', [\App\Http\Controllers\FaceIdController::class, 'searchTeacher'])->name('face.id.search');
Route::post('/face-id/attendance', [\App\Http\Controllers\FaceIdController::class, 'submitAttendance'])->name('face.id.attendance');

// 2. Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// 3. Teacher Auth Routes
Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);
Route::get('/teacher/confirm', [TeacherAuthController::class, 'showConfirmForm'])->name('teacher.confirm');
Route::post('/teacher/confirm', [TeacherAuthController::class, 'confirm']);
Route::post('/teacher/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

// 4. Admin Guard Protected Routes
Route::middleware(['auth:web'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Teacher CRUD Routes
    Route::get('/admin/teachers/export/{format}', [AdminTeacherController::class, 'export'])->name('admin.teachers.export');
    Route::post('/admin/teachers/import', [AdminTeacherController::class, 'import'])->name('admin.teachers.import');
    Route::post('/admin/teachers/{teacher}/restore', [AdminTeacherController::class, 'restore'])->name('admin.teachers.restore');
    Route::resource('/admin/teachers', AdminTeacherController::class)->only([
        'index', 'store', 'update', 'destroy'
    ])->names([
        'index' => 'admin.teachers.index',
        'store' => 'admin.teachers.store',
        'update' => 'admin.teachers.update',
        'destroy' => 'admin.teachers.destroy',
    ]);

    // Settings Routes
    Route::get('/admin/settings/attendance', [AdminSettingsController::class, 'attendance'])->name('admin.settings.attendance');
    Route::get('/admin/settings/gps', [AdminSettingsController::class, 'gps'])->name('admin.settings.gps');
    Route::get('/admin/settings/qr', [AdminSettingsController::class, 'qr'])->name('admin.settings.qr');
    Route::post('/admin/settings/save', [AdminSettingsController::class, 'save'])->name('admin.settings.save');
    Route::get('/admin/settings/qr/token', [AdminSettingsController::class, 'getQrToken'])->name('admin.settings.qr.token');

    // Report Routes
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/export/excel', [AdminReportController::class, 'exportExcel'])->name('admin.reports.export.excel');
    Route::get('/admin/reports/export/pdf', [AdminReportController::class, 'exportPdf'])->name('admin.reports.export.pdf');
    Route::get('/admin/selfies/{filename}', [\App\Http\Controllers\FaceIdController::class, 'showSelfie'])->name('admin.selfies.show');

    // Profile Routes
    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('/admin/profile/password', [AdminProfileController::class, 'editPassword'])->name('admin.profile.password.edit');
    Route::post('/admin/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');

    // Device Management Routes
    Route::get('/admin/devices', [\App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('admin.devices.index');
    Route::post('/admin/devices', [\App\Http\Controllers\Admin\DeviceController::class, 'store'])->name('admin.devices.store');
    Route::post('/admin/devices/{id}/toggle', [\App\Http\Controllers\Admin\DeviceController::class, 'toggle'])->name('admin.devices.toggle');
    Route::post('/admin/devices/{id}/activate-browser', [\App\Http\Controllers\Admin\DeviceController::class, 'activateBrowser'])->name('admin.devices.activate-browser');
    Route::post('/admin/devices/deactivate-browser', [\App\Http\Controllers\Admin\DeviceController::class, 'deactivateBrowser'])->name('admin.devices.deactivate-browser');
    Route::delete('/admin/devices/{id}', [\App\Http\Controllers\Admin\DeviceController::class, 'destroy'])->name('admin.devices.destroy');

    // Face ID Registration Routes
    Route::get('/admin/teachers/{teacher}/face-id', [\App\Http\Controllers\Admin\TeacherController::class, 'showFaceIdRegistration'])->name('admin.teachers.face-id');
    Route::post('/admin/teachers/{teacher}/face-id/register', [\App\Http\Controllers\Admin\TeacherController::class, 'registerFaceId'])->name('admin.teachers.face-id.register');
    Route::delete('/admin/teachers/{teacher}/face-id', [\App\Http\Controllers\Admin\TeacherController::class, 'deleteFaceId'])->name('admin.teachers.face-id.delete');

    // Superadmin Routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/admin/superadmin/dashboard', [\App\Http\Controllers\Admin\SuperadminController::class, 'dashboard'])->name('admin.superadmin.dashboard');
        Route::get('/admin/superadmin/recap', [\App\Http\Controllers\Admin\SuperadminController::class, 'recap'])->name('admin.superadmin.recap');
        Route::get('/admin/superadmin/export/excel', [\App\Http\Controllers\Admin\SuperadminController::class, 'exportExcel'])->name('admin.superadmin.export.excel');
        Route::get('/admin/superadmin/export/pdf', [\App\Http\Controllers\Admin\SuperadminController::class, 'exportPdf'])->name('admin.superadmin.export.pdf');
    });
});

// 5. Teacher Guard Protected Routes
Route::middleware([\App\Http\Middleware\VerifyTeacherSession::class])->group(function () {
    Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index'])->name('teacher.attendance');
    Route::post('/teacher/attendance/submit', [TeacherAttendanceController::class, 'submit'])->name('teacher.attendance.submit');
});
