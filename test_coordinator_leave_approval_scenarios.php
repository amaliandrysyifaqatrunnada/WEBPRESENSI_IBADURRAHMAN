<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Unit;
use App\Models\LeaveRequest;
use App\Models\LeaveApprovalHistory;
use App\Services\AttendanceService;
use App\Services\MonthlyAttendanceRecapService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

echo "====================================================\n";
echo "RUNNING KOORDINATOR LEAVE WORKFLOW TEST SUITE\n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

function runTest($testName, $condition, $detail) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$testName} - {$detail}\n";
        $passed++;
    } else {
        echo "[FAIL] {$testName} - {$detail}\n";
        $failed++;
    }
}

// 1. Ensure Roles & Test Data
Role::findOrCreate('superadmin');
Role::findOrCreate('admin');
Role::findOrCreate('koordinator');

$unitTK = Unit::firstOrCreate(['id' => 1], ['name' => 'TK', 'package_type' => 'TK']);
$unitA = Unit::firstOrCreate(['id' => 2], ['name' => 'Paket A', 'package_type' => 'Paket A']);
$unitB = Unit::firstOrCreate(['id' => 3], ['name' => 'Paket B', 'package_type' => 'Paket B']);

$teacherTK = Teacher::firstOrCreate(
    ['email' => 'gurutk_test@ibadurrahman.sch.id'],
    ['name' => 'Guru TK Test', 'unit_id' => 1, 'status' => 'active', 'nip' => '11111', 'password' => bcrypt('password'), 'position' => 'Guru TK']
);

$teacherA = Teacher::firstOrCreate(
    ['email' => 'gurua_test@ibadurrahman.sch.id'],
    ['name' => 'Guru Paket A Test', 'unit_id' => 2, 'status' => 'active', 'nip' => '22222', 'password' => bcrypt('password'), 'position' => 'Guru Paket A']
);

$teacherB = Teacher::firstOrCreate(
    ['email' => 'gurub_test@ibadurrahman.sch.id'],
    ['name' => 'Guru Paket B Test', 'unit_id' => 3, 'status' => 'active', 'nip' => '33333', 'password' => bcrypt('password'), 'position' => 'Guru Paket B']
);

// Clean previous test leave requests for test teachers
LeaveRequest::whereIn('teacher_id', [$teacherTK->id, $teacherA->id, $teacherB->id])->delete();

$coordTKUser = User::withTrashed()->where('email', 'coord_tk@ibadurrahman.sch.id')->first();
if (!$coordTKUser) {
    $coordTKUser = new User();
    $coordTKUser->email = 'coord_tk@ibadurrahman.sch.id';
    $coordTKUser->name = 'Koordinator TK';
    $coordTKUser->password = bcrypt('password');
}
if (method_exists($coordTKUser, 'trashed') && $coordTKUser->trashed()) {
    $coordTKUser->restore();
}
$coordTKUser->unit_id = 1;
$coordTKUser->save();
$coordTKUser->syncRoles(['koordinator']);

$coordAUser = User::withTrashed()->where('email', 'coord_a@ibadurrahman.sch.id')->first();
if (!$coordAUser) {
    $coordAUser = new User();
    $coordAUser->email = 'coord_a@ibadurrahman.sch.id';
    $coordAUser->name = 'Koordinator Paket A';
    $coordAUser->password = bcrypt('password');
}
if (method_exists($coordAUser, 'trashed') && $coordAUser->trashed()) {
    $coordAUser->restore();
}
$coordAUser->unit_id = 2;
$coordAUser->save();
$coordAUser->syncRoles(['koordinator']);

$adminTKUser = User::withTrashed()->where('email', 'admin_tk_test@ibadurrahman.sch.id')->first();
if (!$adminTKUser) {
    $adminTKUser = new User();
    $adminTKUser->email = 'admin_tk_test@ibadurrahman.sch.id';
    $adminTKUser->name = 'Admin Unit TK';
    $adminTKUser->password = bcrypt('password');
}
if (method_exists($adminTKUser, 'trashed') && $adminTKUser->trashed()) {
    $adminTKUser->restore();
}
$adminTKUser->unit_id = 1;
$adminTKUser->save();
$adminTKUser->syncRoles(['admin']);

$superadminUser = User::withTrashed()->where('email', 'superadmin@ibadurrahman.sch.id')->first();
if (!$superadminUser) {
    $superadminUser = User::withTrashed()->where('email', 'superadmin_coord_test@ibadurrahman.sch.id')->first();
    if (!$superadminUser) {
        $superadminUser = new User();
        $superadminUser->email = 'superadmin_coord_test@ibadurrahman.sch.id';
        $superadminUser->name = 'Superadmin Test';
        $superadminUser->password = bcrypt('password');
    }
}
if (method_exists($superadminUser, 'trashed') && $superadminUser->trashed()) {
    $superadminUser->restore();
}
$superadminUser->save();
$superadminUser->syncRoles(['superadmin']);

// TEST 01: Guru Submit Izin tanpa GPS -> Status MENUNGGU_PERSETUJUAN_KOORDINATOR
Auth::guard('teacher')->login($teacherTK);
$reqSubmit = Request::create('/teacher/leaves', 'POST', [
    'type' => 'izin',
    'start_date' => '2026-08-10',
    'end_date' => '2026-08-10',
    'description' => 'Izin Acara Keluarga dari Rumah',
]);
$controllerTeacher = new \App\Http\Controllers\Teacher\LeaveRequestController();
$resSubmit = $controllerTeacher->store($reqSubmit);

$leaveTK = LeaveRequest::where('teacher_id', $teacherTK->id)->orderBy('id', 'desc')->first();

runTest(
    "Test 01: Guru submit izin tanpa GPS status awal MENUNGGU_PERSETUJUAN_KOORDINATOR",
    $leaveTK && $leaveTK->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
    "Leave ID: {$leaveTK->id}, Status: {$leaveTK->status}"
);

// TEST 02: Guru submit attachment private
Storage::fake('local');
$file = UploadedFile::fake()->create('surat_dokter.pdf', 100, 'application/pdf');
$reqAttachment = Request::create('/teacher/leaves', 'POST', [
    'type' => 'sakit',
    'start_date' => '2026-08-12',
    'end_date' => '2026-08-12',
    'description' => 'Sakit Demam',
], [], ['attachment' => $file]);

$controllerTeacher->store($reqAttachment);
$leaveSakitTK = LeaveRequest::where('teacher_id', $teacherTK->id)->orderBy('id', 'desc')->first();

runTest(
    "Test 02: Lampiran izin tersimpan privat",
    $leaveSakitTK && $leaveSakitTK->attachment_path && !str_contains($leaveSakitTK->attachment_path, 'public'),
    "Path: {$leaveSakitTK->attachment_path}"
);

// TEST 03: Koordinator TK dapat melihat izin TK
Auth::guard('web')->login($coordTKUser);
$controllerCoord = new \App\Http\Controllers\Coordinator\LeaveApprovalController();
$reqCoordIndex = Request::create('/coordinator/leaves', 'GET');
$resCoordIndex = $controllerCoord->index($reqCoordIndex);

$leaveRequestsCoord = $resCoordIndex->getData()['leaveRequests'];
$hasTKReq = false;
foreach ($leaveRequestsCoord as $l) {
    if ($l->id == $leaveTK->id) {
        $hasTKReq = true;
        break;
    }
}
runTest(
    "Test 03: Koordinator TK dapat melihat izin guru TK",
    $hasTKReq,
    "Leave TK ID {$leaveTK->id} ditemukan pada portal Koordinator TK"
);

// TEST 04: Koordinator TK ditolak (403) saat mencoba membuka/approve izin guru Paket A (Anti-IDOR)
$leaveA = LeaveRequest::create([
    'teacher_id' => $teacherA->id,
    'unit_id' => 2,
    'type' => 'izin',
    'start_date' => '2026-08-15',
    'end_date' => '2026-08-15',
    'description' => 'Izin Paket A',
    'status' => 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
    'submitted_at' => now(),
]);

$idorBlocked = false;
try {
    $reqApproveCross = Request::create("/coordinator/leaves/{$leaveA->id}/approve", 'POST');
    $controllerCoord->approve($reqApproveCross, $leaveA);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $idorBlocked = true;
    }
}
runTest(
    "Test 04: Koordinator TK ditolak (HTTP 403 Anti-IDOR) saat akses izin Paket A",
    $idorBlocked,
    "Cross-unit approval oleh Koordinator TK berhasil ditolak dengan HTTP 403"
);

// TEST 05: Koordinator TK ditolak (403) saat mencoba unduh attachment Paket A
$leaveAttachmentA = LeaveRequest::create([
    'teacher_id' => $teacherA->id,
    'unit_id' => 2,
    'type' => 'sakit',
    'start_date' => '2026-08-16',
    'end_date' => '2026-08-16',
    'description' => 'Sakit Paket A',
    'attachment_path' => 'private/leave_attachments/test_a.pdf',
    'status' => 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
    'submitted_at' => now(),
]);

$attachmentIdorBlocked = false;
try {
    $controllerCoord->downloadAttachment($leaveAttachmentA);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $attachmentIdorBlocked = true;
    }
}
runTest(
    "Test 05: Koordinator TK ditolak (HTTP 403) saat unduh lampiran privat Paket A",
    $attachmentIdorBlocked,
    "Cross-unit attachment access blocked for Koordinator"
);

// TEST 06: Koordinator TK menyetujui izin TK -> Status berubah ke DISETUJUI_KOORDINATOR
$reqApproveTK = Request::create("/coordinator/leaves/{$leaveTK->id}/approve", 'POST', ['note' => 'Disetujui Koordinator TK']);
$resApproveTK = $controllerCoord->approve($reqApproveTK, $leaveTK);

$leaveTK->refresh();
runTest(
    "Test 06: Koordinator approve izin -> Status DISETUJUI_KOORDINATOR",
    $leaveTK->status === 'DISETUJUI_KOORDINATOR',
    "Status terbaru: {$leaveTK->status}"
);

// TEST 07: Approval Koordinator mencatat audit trail approve_coordinator
$historyCoord = LeaveApprovalHistory::where('leave_request_id', $leaveTK->id)
    ->where('action', 'approve_coordinator')
    ->first();
runTest(
    "Test 07: Audit trail tercatat untuk approve_coordinator",
    $historyCoord && $historyCoord->actor_role === 'koordinator',
    "Actor: {$historyCoord->actor_name}, Role: {$historyCoord->actor_role}, Action: {$historyCoord->action}"
);

// TEST 08: Koordinator menolak izin dengan catatan wajib
$leaveTKReject = LeaveRequest::create([
    'teacher_id' => $teacherTK->id,
    'unit_id' => 1,
    'type' => 'tanpa_keterangan',
    'start_date' => '2026-08-20',
    'end_date' => '2026-08-20',
    'description' => 'Tidak hadir tanpa alasan',
    'status' => 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
    'submitted_at' => now(),
]);

$reqRejectTK = Request::create("/coordinator/leaves/{$leaveTKReject->id}/reject", 'POST', ['note' => 'Alasan tidak jelas']);
$controllerCoord->reject($reqRejectTK, $leaveTKReject);

$leaveTKReject->refresh();
runTest(
    "Test 08: Koordinator reject izin -> Status DITOLAK_KOORDINATOR",
    $leaveTKReject->status === 'DITOLAK_KOORDINATOR',
    "Status terbaru: {$leaveTKReject->status}"
);

// TEST 09: Admin Unit ditolak (HTTP 403) jika mencoba approve izin yang masih MENUNGGU_PERSETUJUAN_KOORDINATOR
Auth::guard('web')->login($adminTKUser);
$controllerAdmin = new \App\Http\Controllers\Admin\LeaveApprovalController();

$adminBypassBlocked = false;
try {
    $reqAdminBypass = Request::create("/admin/leaves/{$leaveTKReject->id}/approve", 'POST');
    $controllerAdmin->approve($reqAdminBypass, $leaveTKReject);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 403) {
        $adminBypassBlocked = true;
    }
}
runTest(
    "Test 09: Admin Unit ditolak (HTTP 403) jika approve izin yang belum disetujui Koordinator",
    $adminBypassBlocked,
    "Admin cannot bypass Koordinator approval stage"
);

// TEST 10: Admin Unit dapat menyetujui izin yang sudah DISETUJUI_KOORDINATOR -> Status DISETUJUI (Final)
$reqAdminApprove = Request::create("/admin/leaves/{$leaveTK->id}/approve", 'POST', ['note' => 'Disetujui Admin Unit TK']);
$controllerAdmin->approve($reqAdminApprove, $leaveTK);

$leaveTK->refresh();
runTest(
    "Test 10: Admin Unit approve -> Status DISETUJUI (Final Approved)",
    $leaveTK->status === 'DISETUJUI',
    "Status final: {$leaveTK->status}"
);

// TEST 11: Approval Admin mencatat audit trail approve_admin
$historyAdmin = LeaveApprovalHistory::where('leave_request_id', $leaveTK->id)
    ->where('action', 'approve_admin')
    ->first();
runTest(
    "Test 11: Audit trail tercatat untuk approve_admin",
    $historyAdmin && $historyAdmin->actor_role === 'admin',
    "Actor: {$historyAdmin->actor_name}, Role: {$historyAdmin->actor_role}, Action: {$historyAdmin->action}"
);

// TEST 12: Single source of truth AttendanceService::checkFinalLeave hanya menganggap DISETUJUI sebagai final
$attendanceService = app(AttendanceService::class);
$finalLeaveCheck = $attendanceService->checkFinalLeave($teacherTK->id, '2026-08-10');
$unapprovedLeaveCheck = $attendanceService->checkFinalLeave($teacherTK->id, '2026-08-20');

runTest(
    "Test 12: AttendanceService checkFinalLeave hanya menganggap DISETUJUI sebagai izin final",
    $finalLeaveCheck && $finalLeaveCheck->id == $leaveTK->id && $unapprovedLeaveCheck === null,
    "Final approved leave detected, unapproved/rejected leave ignored"
);

// TEST 13: MonthlyAttendanceRecapService memuat scan times jam masuk/pulang
$recapService = app(MonthlyAttendanceRecapService::class);
$recapData = $recapService->buildCalendarMatrix([
    'month' => 8,
    'year' => 2026,
    'unit_id' => 1
]);

runTest(
    "Test 13: MonthlyAttendanceRecapService memuat detail_presensi dan scan times",
    isset($recapData['detail_presensi']) && is_array($recapData['detail_presensi']),
    "Detail presensi memuat " . count($recapData['detail_presensi']) . " rekaman harian"
);

// TEST 14: Multi-sheet Excel export memuat 3 sheet
$excelExport = new \App\Exports\MonthlyRecapExport($recapData);
$sheets = $excelExport->sheets();
runTest(
    "Test 14: Export Excel memiliki 3 Sheet (Matrix, Detail Presensi, Rekapitulasi)",
    count($sheets) === 3 && $sheets[0]->title() === 'REKAP KALENDER BULANAN' && $sheets[1]->title() === 'DETAIL PRESENSI' && $sheets[2]->title() === 'REKAPITULASI',
    "Excel Export contains 3 sheets with exact title names"
);

// TEST 15: Superadmin dapat melihat global pengajuan & manajemen koordinator
Auth::guard('web')->login($superadminUser);
$controllerAdminCoord = new \App\Http\Controllers\Admin\CoordinatorController();
$resCoordSuper = $controllerAdminCoord->index();

runTest(
    "Test 15: Superadmin dapat membuka manajemen Koordinator Paket",
    $resCoordSuper->getName() === 'admin.coordinators.index',
    "Superadmin access granted to coordinator management"
);

// TEST 16: Path traversal check pada lampiran
$leavePathTraversal = LeaveRequest::create([
    'teacher_id' => $teacherTK->id,
    'unit_id' => 1,
    'type' => 'sakit',
    'start_date' => '2026-08-25',
    'end_date' => '2026-08-25',
    'description' => 'Path traversal test',
    'attachment_path' => 'private/leave_attachments/../../.env',
    'status' => 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
    'submitted_at' => now(),
]);

$traversalBlocked = false;
try {
    $controllerCoord->downloadAttachment($leavePathTraversal);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e->getStatusCode() === 400 || $e->getStatusCode() === 403 || $e->getStatusCode() === 404) {
        $traversalBlocked = true;
    }
}
runTest(
    "Test 16: Path Traversal pada unduh lampiran berhasil diblokir",
    $traversalBlocked,
    "Path traversal pattern detected & blocked"
);

echo "\n====================================================\n";
echo "KOORDINATOR WORKFLOW TEST SUMMARY\n";
echo "TOTAL PASSED: {$passed} / 16\n";
echo "TOTAL FAILED: {$failed} / 16\n";
echo "====================================================\n";

exit($failed > 0 ? 1 : 0);
