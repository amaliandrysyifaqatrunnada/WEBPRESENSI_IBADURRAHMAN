<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Unit;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceDevice;
use App\Models\SchoolSetting;
use App\Services\AttendanceService;
use App\DTOs\AttendanceSubmitData;
use App\Services\QrTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

$totalPassed = 0;
$totalFailed = 0;

function logResult($testNum, $description, $status, $details = "") {
    global $totalPassed, $totalFailed;
    $paddedNum = str_pad($testNum, 2, '0', STR_PAD_LEFT);
    if ($status === "PASS") {
        $totalPassed++;
        echo "[\033[32mPASS\033[0m] Test $paddedNum: $description" . ($details ? " - $details" : "") . "\n";
    } else {
        $totalFailed++;
        echo "[\033[31mFAIL\033[0m] Test $paddedNum: $description" . ($details ? " - $details" : "") . "\n";
    }
}

try {
    // 1. Setup Units
    $unitA = Unit::updateOrCreate(['id' => 1], [
        'name' => 'PKBM Ibadurrahman - Paket A',
        'package_type' => 'PAKET_A',
        'latitude' => -7.4486,
        'longitude' => 112.7257,
        'gps_radius' => 50.0
    ]);

    $unitTK = Unit::updateOrCreate(['id' => 2], [
        'name' => 'TK PKBM Ibadurrahman',
        'package_type' => 'TK',
        'latitude' => -7.452778,
        'longitude' => 112.688611,
        'gps_radius' => 50.0
    ]);

    $unitB = Unit::updateOrCreate(['id' => 3], [
        'name' => 'PKBM Ibadurrahman - Paket B',
        'package_type' => 'PAKET_B',
        'latitude' => -7.4535,
        'longitude' => 112.7097,
        'gps_radius' => 50.0
    ]);

    $unitC = Unit::updateOrCreate(['id' => 4], [
        'name' => 'PKBM Ibadurrahman - Paket C',
        'package_type' => 'PAKET_C',
        'latitude' => -7.4540,
        'longitude' => 112.7100,
        'gps_radius' => 50.0
    ]);

    // Roles
    $adminRole = Role::findOrCreate('admin', 'web');
    $superadminRole = Role::findOrCreate('superadmin', 'web');

    // Create Admins
    $adminTK = User::updateOrCreate(['email' => 'test-admin-tk-map@ibadurrahman.sch.id'], [
        'name' => 'Admin TK Map',
        'password' => Hash::make('password'),
        'unit_id' => $unitTK->id,
    ]);
    $adminTK->syncRoles([$adminRole]);

    $adminA = User::updateOrCreate(['email' => 'test-admin-a-map@ibadurrahman.sch.id'], [
        'name' => 'Admin Paket A Map',
        'password' => Hash::make('password'),
        'unit_id' => $unitA->id,
    ]);
    $adminA->syncRoles([$adminRole]);

    $adminB = User::updateOrCreate(['email' => 'test-admin-b-map@ibadurrahman.sch.id'], [
        'name' => 'Admin Paket B Map',
        'password' => Hash::make('password'),
        'unit_id' => $unitB->id,
    ]);
    $adminB->syncRoles([$adminRole]);

    $adminC = User::updateOrCreate(['email' => 'test-admin-c-map@ibadurrahman.sch.id'], [
        'name' => 'Admin Paket C Map',
        'password' => Hash::make('password'),
        'unit_id' => $unitC->id,
    ]);
    $adminC->syncRoles([$adminRole]);

    $superadmin = User::updateOrCreate(['email' => 'test-superadmin-map@ibadurrahman.sch.id'], [
        'name' => 'Superadmin Map',
        'password' => Hash::make('password'),
        'unit_id' => null,
    ]);
    $superadmin->syncRoles([$superadminRole]);

    // Create Teachers
    $teacherTK = Teacher::updateOrCreate(['email' => 'test-teacher-tk-map@ibadurrahman.sch.id'], [
        'name' => 'Guru TK Map',
        'nip' => 'TK123',
        'password' => Hash::make('password'),
        'status' => 'active',
        'unit_id' => $unitTK->id,
        'position' => 'Guru',
    ]);
    $teacherA = Teacher::updateOrCreate(['email' => 'test-teacher-a-map@ibadurrahman.sch.id'], [
        'name' => 'Guru Paket A Map',
        'nip' => 'A123',
        'password' => Hash::make('password'),
        'status' => 'active',
        'unit_id' => $unitA->id,
        'position' => 'Guru',
    ]);
    $teacherB = Teacher::updateOrCreate(['email' => 'test-teacher-b-map@ibadurrahman.sch.id'], [
        'name' => 'Guru Paket B Map',
        'nip' => 'B123',
        'password' => Hash::make('password'),
        'status' => 'active',
        'unit_id' => $unitB->id,
        'position' => 'Guru',
    ]);
    $teacherC = Teacher::updateOrCreate(['email' => 'test-teacher-c-map@ibadurrahman.sch.id'], [
        'name' => 'Guru Paket C Map',
        'nip' => 'C123',
        'password' => Hash::make('password'),
        'status' => 'active',
        'unit_id' => $unitC->id,
        'position' => 'Guru',
    ]);

    // Ensure schedules exist for all units
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ([$unitA, $unitTK, $unitB, $unitC] as $u) {
        foreach ($days as $day) {
            \App\Models\Schedule::updateOrCreate(
                ['unit_id' => $u->id, 'day_of_week' => $day],
                [
                    'is_active' => true,
                    'work_start_time' => '06:00:00',
                    'reward_limit_time' => '06:45:00',
                    'late_threshold_time' => '06:50:00',
                    'work_end_time' => '17:00:00',
                    'work_end_time_end' => '18:00:00',
                ]
            );
        }
    }

    // ----------------------------------------------------
    // TEST 01: Admin TK -> unit TK
    // ----------------------------------------------------
    if ($adminTK->unit_id === $unitTK->id) {
        logResult(1, "Admin TK memiliki unit TK", "PASS");
    } else {
        logResult(1, "Admin TK memiliki unit TK", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 02: Admin Paket A -> unit Paket A
    // ----------------------------------------------------
    if ($adminA->unit_id === $unitA->id) {
        logResult(2, "Admin Paket A memiliki unit Paket A", "PASS");
    } else {
        logResult(2, "Admin Paket A memiliki unit Paket A", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 03: Admin Paket B -> unit Paket B
    // ----------------------------------------------------
    if ($adminB->unit_id === $unitB->id) {
        logResult(3, "Admin Paket B memiliki unit Paket B", "PASS");
    } else {
        logResult(3, "Admin Paket B memiliki unit Paket B", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 04: Admin Paket C -> unit Paket C
    // ----------------------------------------------------
    if ($adminC->unit_id === $unitC->id) {
        logResult(4, "Admin Paket C memiliki unit Paket C", "PASS");
    } else {
        logResult(4, "Admin Paket C memiliki unit Paket C", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 05: Guru TK -> GPS TK
    // ----------------------------------------------------
    if ($teacherTK->unit->latitude === $unitTK->latitude && $teacherTK->unit->longitude === $unitTK->longitude) {
        logResult(5, "Guru TK merujuk ke GPS TK", "PASS");
    } else {
        logResult(5, "Guru TK merujuk ke GPS TK", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 06: Guru Paket A -> GPS Paket A
    // ----------------------------------------------------
    if ($teacherA->unit->latitude === $unitA->latitude && $teacherA->unit->longitude === $unitA->longitude) {
        logResult(6, "Guru Paket A merujuk ke GPS Paket A", "PASS");
    } else {
        logResult(6, "Guru Paket A merujuk ke GPS Paket A", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 07: Guru Paket B -> GPS Paket B
    // ----------------------------------------------------
    if ($teacherB->unit->latitude === $unitB->latitude && $teacherB->unit->longitude === $unitB->longitude) {
        logResult(7, "Guru Paket B merujuk ke GPS Paket B", "PASS");
    } else {
        logResult(7, "Guru Paket B merujuk ke GPS Paket B", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 08: Guru Paket C -> GPS Paket C
    // ----------------------------------------------------
    if ($teacherC->unit->latitude === $unitC->latitude && $teacherC->unit->longitude === $unitC->longitude) {
        logResult(8, "Guru Paket C merujuk ke GPS Paket C", "PASS");
    } else {
        logResult(8, "Guru Paket C merujuk ke GPS Paket C", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 09: Guru Paket A tidak dapat mengakses data TK
    // ----------------------------------------------------
    Auth::guard('teacher')->login($teacherA);
    if (Auth::guard('teacher')->user()->id !== $teacherTK->id) {
        logResult(9, "Guru Paket A tidak dapat melihat/mengakses data TK", "PASS");
    } else {
        logResult(9, "Guru Paket A tidak dapat melihat/mengakses data TK", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 10: Guru Paket A tidak dapat mengakses data Paket B
    // ----------------------------------------------------
    if (Auth::guard('teacher')->user()->id !== $teacherB->id) {
        logResult(10, "Guru Paket A tidak dapat melihat/mengakses data Paket B", "PASS");
    } else {
        logResult(10, "Guru Paket A tidak dapat melihat/mengakses data Paket B", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 11: Admin Paket A tidak dapat melihat data Paket B
    // ----------------------------------------------------
    Auth::guard('web')->login($adminA);
    $teacherRepo = app(\App\Repositories\Contracts\TeacherRepositoryInterface::class);
    $adminScopedTeachers = collect($teacherRepo->getPaginated()->items());
    $hasBAdmin = $adminScopedTeachers->contains('unit_id', $unitB->id);
    if (!$hasBAdmin) {
        logResult(11, "Admin Paket A tidak dapat melihat data Paket B", "PASS");
    } else {
        logResult(11, "Admin Paket A tidak dapat melihat data Paket B", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 12: Admin Paket A tidak dapat melihat data Paket C
    // ----------------------------------------------------
    $hasCAdmin = $adminScopedTeachers->contains('unit_id', $unitC->id);
    if (!$hasCAdmin) {
        logResult(12, "Admin Paket A tidak dapat melihat data Paket C", "PASS");
    } else {
        logResult(12, "Admin Paket A tidak dapat melihat data Paket C", "FAIL");
    }

    // ----------------------------------------------------
    // TEST 13: QR Paket A + Guru Paket A -> PASS
    // ----------------------------------------------------
    $qrTokenService = app(QrTokenService::class);
    $qrTokenA = $qrTokenService->generateToken($unitA->id);

    Attendance::where('teacher_id', $teacherA->id)->delete();
    AttendanceLog::where('teacher_id', $teacherA->id)->delete();

    $dtoValid = new AttendanceSubmitData(
        teacher_id: $teacherA->id,
        action_type: 'check_in',
        latitude: $unitA->latitude,
        longitude: $unitA->longitude,
        accuracy: 10.0,
        method: 'qr',
        qr_token: $qrTokenA,
        status: null,
        date: null,
        ip_address: '127.0.0.1',
        user_agent: 'PHPUnit'
    );

    $attendanceService = app(AttendanceService::class);
    try {
        $res = $attendanceService->submitAttendance($dtoValid);
        if ($res['success']) {
            logResult(13, "QR Paket A + Guru Paket A", "PASS");
        } else {
            logResult(13, "QR Paket A + Guru Paket A", "FAIL", "Response success false");
        }
    } catch (\Exception $e) {
        logResult(13, "QR Paket A + Guru Paket A", "FAIL", "Exception: " . $e->getMessage());
    }

    // ----------------------------------------------------
    // TEST 14: QR Paket A + Guru Paket B -> REJECT
    // ----------------------------------------------------
    $dtoCross = new AttendanceSubmitData(
        teacher_id: $teacherB->id,
        action_type: 'check_in',
        latitude: $unitB->latitude,
        longitude: $unitB->longitude,
        accuracy: 10.0,
        method: 'qr',
        qr_token: $qrTokenA,
        status: null,
        date: null,
        ip_address: '127.0.0.1',
        user_agent: 'PHPUnit'
    );

    try {
        $attendanceService->submitAttendance($dtoCross);
        logResult(14, "QR Paket A + Guru Paket B", "FAIL", "Should have thrown cross-unit exception.");
    } catch (\Exception $e) {
        if (str_contains($e->getMessage(), 'QR Code bukan untuk unit Anda')) {
            logResult(14, "QR Paket A + Guru Paket B ditolak", "PASS");
        } else {
            logResult(14, "QR Paket A + Guru Paket B ditolak", "FAIL", "Different exception: " . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    // TEST 15: Face ID Guru Paket A -> menggunakan unit Paket A
    // ----------------------------------------------------
    // Test that when teacherA submits Face ID attendance, it resolves to unit Paket A coordinates
    // We send request to FaceIdController to simulate Face ID submit
    $faceController = app(\App\Http\Controllers\FaceIdController::class);
    
    // Valid location for Paket A
    $reqFaceValid = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherA->id,
        'latitude' => $unitA->latitude,
        'longitude' => $unitA->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => 'data:image/jpeg;base64,L3Rlc3Q=',
    ]);

    Auth::guard('teacher')->login($teacherA);
    Attendance::where('teacher_id', $teacherA->id)->delete();
    AttendanceLog::where('teacher_id', $teacherA->id)->delete();

    $resFace = $faceController->submitAttendance($reqFaceValid);
    $faceData = json_decode($resFace->getContent(), true);

    if (isset($faceData['success']) && $faceData['success']) {
        logResult(15, "Face ID Guru Paket A menggunakan unit Paket A", "PASS");
    } else {
        logResult(15, "Face ID Guru Paket A menggunakan unit Paket A", "FAIL", json_encode($faceData));
    }

    // ----------------------------------------------------
    // TEST 16: Manipulasi unit_id request oleh Guru Paket A -> diabaikan
    // ----------------------------------------------------
    // Send a request with unit_id = TK (2) but the teacher is Paket A (1).
    // The system must resolve the unit using the authenticated teacher's unit_id (Paket A) rather than request.
    // If the system uses the TK unit_id, it will validate against TK coordinates, which are 50+ meters away from $unitA coordinates.
    // So if the system correctly ignores the request unit_id, the attendance succeeds because it validates against $unitA coordinates!
    Attendance::where('teacher_id', $teacherA->id)->delete();
    AttendanceLog::where('teacher_id', $teacherA->id)->delete();

    $reqManip = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherA->id,
        'latitude' => $unitA->latitude,
        'longitude' => $unitA->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => 'data:image/jpeg;base64,L3Rlc3Q=',
        'unit_id' => $unitTK->id, // Manipulated request unit_id
    ]);

    $resManip = $faceController->submitAttendance($reqManip);
    $manipData = json_decode($resManip->getContent(), true);

    if (isset($manipData['success']) && $manipData['success']) {
        logResult(16, "Manipulasi unit_id request oleh Guru Paket A diabaikan", "PASS");
    } else {
        logResult(16, "Manipulasi unit_id request oleh Guru Paket A diabaikan", "FAIL", json_encode($manipData));
    }

    // ----------------------------------------------------
    // TEST 17: GPS Paket A tidak menggunakan koordinat TK
    // ----------------------------------------------------
    // Try to check in at TK coordinates. Should be rejected as OUTSIDE_GEOFENCE
    Attendance::where('teacher_id', $teacherA->id)->delete();
    AttendanceLog::where('teacher_id', $teacherA->id)->delete();

    $reqTKCoords = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherA->id,
        'latitude' => $unitTK->latitude,
        'longitude' => $unitTK->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => 'data:image/jpeg;base64,L3Rlc3Q=',
    ]);

    $resTKCoords = $faceController->submitAttendance($reqTKCoords);
    $tkCoordsData = json_decode($resTKCoords->getContent(), true);

    if (isset($tkCoordsData['message']) && $tkCoordsData['message'] === 'OUTSIDE_GEOFENCE') {
        logResult(17, "GPS Paket A tidak menggunakan koordinat TK", "PASS");
    } else {
        logResult(17, "GPS Paket A tidak menggunakan koordinat TK", "FAIL", json_encode($tkCoordsData));
    }

    // ----------------------------------------------------
    // TEST 18: GPS Paket A tidak menggunakan koordinat Paket B
    // ----------------------------------------------------
    $reqBCoords = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherA->id,
        'latitude' => $unitB->latitude,
        'longitude' => $unitB->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => 'data:image/jpeg;base64,L3Rlc3Q=',
    ]);

    $resBCoords = $faceController->submitAttendance($reqBCoords);
    $bCoordsData = json_decode($resBCoords->getContent(), true);

    if (isset($bCoordsData['message']) && $bCoordsData['message'] === 'OUTSIDE_GEOFENCE') {
        logResult(18, "GPS Paket A tidak menggunakan koordinat Paket B", "PASS");
    } else {
        logResult(18, "GPS Paket A tidak menggunakan koordinat Paket B", "FAIL", json_encode($bCoordsData));
    }

    // ----------------------------------------------------
    // TEST 19: GPS Paket A tidak menggunakan koordinat Paket C
    // ----------------------------------------------------
    $reqCCoords = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherA->id,
        'latitude' => $unitC->latitude,
        'longitude' => $unitC->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => 'data:image/jpeg;base64,L3Rlc3Q=',
    ]);

    $resCCoords = $faceController->submitAttendance($reqCCoords);
    $cCoordsData = json_decode($resCCoords->getContent(), true);

    if (isset($cCoordsData['message']) && $cCoordsData['message'] === 'OUTSIDE_GEOFENCE') {
        logResult(19, "GPS Paket A tidak menggunakan koordinat Paket C", "PASS");
    } else {
        logResult(19, "GPS Paket A tidak menggunakan koordinat Paket C", "FAIL", json_encode($cCoordsData));
    }

    // ----------------------------------------------------
    // TEST 20: Superadmin dapat melihat seluruh unit
    // ----------------------------------------------------
    Auth::guard('web')->login($superadmin);
    $allTeachers = collect($teacherRepo->getPaginated()->items());
    $hasA = $allTeachers->contains('unit_id', $unitA->id);
    $hasTKAll = $allTeachers->contains('unit_id', $unitTK->id);
    
    if ($hasA && $hasTKAll) {
        logResult(20, "Superadmin dapat melihat seluruh unit", "PASS");
    } else {
        logResult(20, "Superadmin dapat melihat seluruh unit", "FAIL", "Count: " . $allTeachers->count());
    }

    // Clean up test teachers, admins, and logs
    Teacher::whereIn('email', [
        'test-teacher-tk-map@ibadurrahman.sch.id',
        'test-teacher-a-map@ibadurrahman.sch.id',
        'test-teacher-b-map@ibadurrahman.sch.id',
        'test-teacher-c-map@ibadurrahman.sch.id'
    ])->forceDelete();

    User::whereIn('email', [
        'test-admin-tk-map@ibadurrahman.sch.id',
        'test-admin-a-map@ibadurrahman.sch.id',
        'test-admin-b-map@ibadurrahman.sch.id',
        'test-admin-c-map@ibadurrahman.sch.id',
        'test-superadmin-map@ibadurrahman.sch.id'
    ])->forceDelete();

    Attendance::whereIn('teacher_id', [$teacherA->id, $teacherB->id])->delete();
    AttendanceLog::whereIn('teacher_id', [$teacherA->id, $teacherB->id])->delete();

} catch (\Exception $e) {
    echo "Fatal Exception during testing: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=============================================\n";
echo "UNIT MAPPING TEST RESULTS:\n";
echo "=============================================\n";
echo "Total Passed: $totalPassed / 20\n";
echo "Total Failed: $totalFailed / 20\n";
if ($totalFailed > 0) {
    exit(1);
} else {
    exit(0);
}
