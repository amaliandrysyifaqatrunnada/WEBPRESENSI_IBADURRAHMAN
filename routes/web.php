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
Route::get('/face-id/teachers', [\App\Http\Controllers\FaceIdController::class, 'getTeachersByUnit'])->name('face.id.teachers');
Route::post('/face-id/search', [\App\Http\Controllers\FaceIdController::class, 'searchTeacher'])->name('face.id.search');
Route::post('/face-id/attendance', [\App\Http\Controllers\FaceIdController::class, 'submitAttendance'])->name('face.id.attendance');

// 2. Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::match(['get', 'post'], '/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// 3. Teacher Auth Routes
Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);
Route::get('/teacher/confirm', [TeacherAuthController::class, 'showConfirmForm'])->name('teacher.confirm');
Route::post('/teacher/confirm', [TeacherAuthController::class, 'confirm']);
Route::match(['get', 'post'], '/teacher/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

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

    // Teacher Custom Work Schedule Routes
    Route::get('/admin/teachers-schedule', [\App\Http\Controllers\Admin\TeacherScheduleController::class, 'index'])->name('admin.teachers.schedule.index');
    Route::get('/admin/teachers/{teacher}/schedule', [\App\Http\Controllers\Admin\TeacherScheduleController::class, 'edit'])->name('admin.teachers.schedule.edit');
    Route::put('/admin/teachers/{teacher}/schedule', [\App\Http\Controllers\Admin\TeacherScheduleController::class, 'update'])->name('admin.teachers.schedule.update');

    // Leave Approval Routes
    Route::get('/admin/leaves', [\App\Http\Controllers\Admin\LeaveApprovalController::class, 'index'])->name('admin.leaves.index');
    Route::post('/admin/leaves/{leaveRequest}/approve', [\App\Http\Controllers\Admin\LeaveApprovalController::class, 'approve'])->name('admin.leaves.approve');
    Route::post('/admin/leaves/{leaveRequest}/reject', [\App\Http\Controllers\Admin\LeaveApprovalController::class, 'reject'])->name('admin.leaves.reject');
    Route::get('/admin/leaves/{leaveRequest}/attachment', [\App\Http\Controllers\Admin\LeaveApprovalController::class, 'downloadAttachment'])->name('admin.leaves.attachment');

    // Superadmin Routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/admin/superadmin/dashboard', [\App\Http\Controllers\Admin\SuperadminController::class, 'dashboard'])->name('admin.superadmin.dashboard');
        Route::get('/admin/superadmin/recap', [\App\Http\Controllers\Admin\SuperadminController::class, 'recap'])->name('admin.superadmin.recap');
        Route::get('/admin/superadmin/export/excel', [\App\Http\Controllers\Admin\SuperadminController::class, 'exportExcel'])->name('admin.superadmin.export.excel');
        Route::get('/admin/superadmin/export/pdf', [\App\Http\Controllers\Admin\SuperadminController::class, 'exportPdf'])->name('admin.superadmin.export.pdf');

        // Holiday Management Routes
        Route::get('/admin/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('admin.holidays.index');
        Route::post('/admin/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('admin.holidays.store');
        Route::post('/admin/holidays/import', [\App\Http\Controllers\Admin\HolidayController::class, 'import'])->name('admin.holidays.import');
        Route::get('/admin/holidays/template', [\App\Http\Controllers\Admin\HolidayController::class, 'downloadTemplate'])->name('admin.holidays.template');
        Route::put('/admin/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('admin.holidays.update');
        Route::delete('/admin/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('admin.holidays.destroy');

        // Coordinator Management Routes
        Route::get('/admin/coordinators', [\App\Http\Controllers\Admin\CoordinatorController::class, 'index'])->name('admin.coordinators.index');
        Route::post('/admin/coordinators', [\App\Http\Controllers\Admin\CoordinatorController::class, 'store'])->name('admin.coordinators.store');
        Route::delete('/admin/coordinators/{user}', [\App\Http\Controllers\Admin\CoordinatorController::class, 'destroy'])->name('admin.coordinators.destroy');
    });
});

// 5. Coordinator Guard Protected Routes
Route::middleware(['auth:web', 'role:koordinator|superadmin'])->prefix('coordinator')->as('coordinator.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Coordinator\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leaves', [\App\Http\Controllers\Coordinator\LeaveApprovalController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/{leaveRequest}', [\App\Http\Controllers\Coordinator\LeaveApprovalController::class, 'show'])->name('leaves.show');
    Route::post('/leaves/{leaveRequest}/approve', [\App\Http\Controllers\Coordinator\LeaveApprovalController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leaveRequest}/reject', [\App\Http\Controllers\Coordinator\LeaveApprovalController::class, 'reject'])->name('leaves.reject');
    Route::get('/leaves/{leaveRequest}/attachment', [\App\Http\Controllers\Coordinator\LeaveApprovalController::class, 'downloadAttachment'])->name('leaves.attachment');
});

// 5. Teacher Guard Protected Routes
Route::middleware([\App\Http\Middleware\VerifyTeacherSession::class])->group(function () {
    Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index'])->name('teacher.attendance');
    Route::post('/teacher/attendance/submit', [TeacherAttendanceController::class, 'submit'])->name('teacher.attendance.submit');
    
    // Teacher Leave Request Routes
    Route::get('/teacher/leaves', [\App\Http\Controllers\Teacher\LeaveRequestController::class, 'index'])->name('teacher.leaves.index');
    Route::post('/teacher/leaves', [\App\Http\Controllers\Teacher\LeaveRequestController::class, 'store'])->name('teacher.leaves.store');
    Route::get('/teacher/leaves/{leaveRequest}/attachment', [\App\Http\Controllers\Teacher\LeaveRequestController::class, 'downloadAttachment'])->name('teacher.leaves.attachment');
});
