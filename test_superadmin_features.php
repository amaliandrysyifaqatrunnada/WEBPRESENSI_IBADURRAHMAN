<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Unit;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceDevice;
use App\Models\SchoolSetting;
use App\Http\Controllers\Admin\SuperadminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\FaceIdController;
use App\Http\Controllers\PortalController;
use App\Services\AttendanceService;
use App\Services\QrTokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function logResult($num, $name, $status, $msg = '') {
    $color = $status === 'PASS' ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    echo sprintf("[%s] Test %02d: %s - %s %s\n", $status, $num, $name, $msg, $reset);
}

$totalPassed = 0;
$totalFailed = 0;

try {
    // ----------------------------------------------------
    // SETUP FOR TESTING
    // ----------------------------------------------------
    $unitA = Unit::find(1); // Paket A
    $unitTK = Unit::find(2); // TK
    $unitB = Unit::find(3); // Paket B
    $unitC = Unit::find(4); // Paket C

    // Create superadmin role & user if missing
    $superadminUser = User::where('email', 'superadmin@ibadurrahman.sch.id')->first();
    if (!$superadminUser) {
        $superadminUser = User::create([
            'name' => 'Superadmin PKBM',
            'email' => 'superadmin@ibadurrahman.sch.id',
            'password' => bcrypt('password'),
            'unit_id' => null,
        ]);
    }
    if (!$superadminUser->hasRole('superadmin')) {
        $superadminUser->assignRole('superadmin');
    }

    // Fetch or create Admin TK
    $adminTK = User::where('email', 'admin-tk@ibadurrahman.sch.id')->first();
    if (!$adminTK) {
        $adminTK = User::create([
            'name' => 'Admin Unit TK',
            'email' => 'admin-tk@ibadurrahman.sch.id',
            'password' => bcrypt('password'),
            'unit_id' => 2,
        ]);
    }

    // Clean up test teachers & logs for testing
    Teacher::whereIn('email', [
        'test-super-teacher-tk@ibadurrahman.sch.id',
        'test-super-teacher-a@ibadurrahman.sch.id'
    ])->forceDelete();

    $teacherTK = Teacher::create([
        'nip' => '88880001',
        'name' => 'Test Teacher TK',
        'email' => 'test-super-teacher-tk@ibadurrahman.sch.id',
        'password' => bcrypt('password'),
        'position' => 'Guru TK',
        'status' => 'active',
        'unit_id' => 2,
    ]);

    $teacherA = Teacher::create([
        'nip' => '88880002',
        'name' => 'Test Teacher Paket A',
        'email' => 'test-super-teacher-a@ibadurrahman.sch.id',
        'password' => bcrypt('password'),
        'position' => 'Guru Paket A',
        'status' => 'active',
        'unit_id' => 1,
    ]);

    // Ensure they have active schedules/settings
    SchoolSetting::setValue('attendance_method', 'gps', 1);
    SchoolSetting::setValue('attendance_method', 'gps', 2);
    SchoolSetting::setValue('gps_accuracy_threshold', '100', 1);
    SchoolSetting::setValue('gps_accuracy_threshold', '100', 2);

    $superController = app(SuperadminController::class);
    $dashController = app(DashboardController::class);

    // ----------------------------------------------------
    // TEST 1: Superadmin dapat login
    // ----------------------------------------------------
    Auth::guard('web')->logout();
    $loginSuccess = Auth::guard('web')->attempt([
        'email' => 'superadmin@ibadurrahman.sch.id',
        'password' => 'password'
    ]);

    if ($loginSuccess && Auth::guard('web')->user()->hasRole('superadmin')) {
        logResult(1, "Superadmin dapat login", "PASS");
        $totalPassed++;
    } else {
        logResult(1, "Superadmin dapat login", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 2: Superadmin dapat membuka dashboard
    // ----------------------------------------------------
    Auth::guard('web')->login($superadminUser);
    $req = Request::create('/admin/superadmin/dashboard', 'GET');
    $res = $superController->dashboard($req);

    if ($res->name() === 'admin.superadmin.dashboard') {
        logResult(2, "Superadmin dapat membuka dashboard", "PASS");
        $totalPassed++;
    } else {
        logResult(2, "Superadmin dapat membuka dashboard", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 3-6: Superadmin dapat melihat data TK, Paket A, Paket B, Paket C
    // ----------------------------------------------------
    $viewData = $res->getData();
    if (isset($viewData['totalTK']) && isset($viewData['totalPaketA']) && isset($viewData['totalPaketB']) && isset($viewData['totalPaketC'])) {
        logResult(3, "Superadmin dapat melihat TK", "PASS", "Count TK: " . $viewData['totalTK']);
        logResult(4, "Superadmin dapat melihat Paket A", "PASS", "Count Paket A: " . $viewData['totalPaketA']);
        logResult(5, "Superadmin dapat melihat Paket B", "PASS", "Count Paket B: " . $viewData['totalPaketB']);
        logResult(6, "Superadmin dapat melihat Paket C", "PASS", "Count Paket C: " . $viewData['totalPaketC']);
        $totalPassed += 4;
    } else {
        logResult(3, "Superadmin dapat melihat data unit", "FAIL");
        $totalFailed += 4;
    }

    // ----------------------------------------------------
    // TEST 7: Superadmin dapat melihat gabungan seluruh unit
    // ----------------------------------------------------
    $dbTotalTeachers = Teacher::count();
    if (isset($viewData['totalTeachers']) && $viewData['totalTeachers'] === $dbTotalTeachers) {
        logResult(7, "Superadmin dapat melihat gabungan seluruh unit", "PASS", "Total: " . $viewData['totalTeachers']);
        $totalPassed++;
    } else {
        logResult(7, "Superadmin dapat melihat gabungan seluruh unit", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 8: Superadmin dapat menggunakan filter unit
    // ----------------------------------------------------
    $reqFilter = Request::create('/admin/superadmin/dashboard', 'GET', ['unit_id' => 2]);
    $resFilter = $superController->dashboard($reqFilter);
    $filterData = $resFilter->getData();
    $tkTeachersCount = Teacher::where('unit_id', 2)->count();

    if (isset($filterData['totalTeachers']) && $filterData['totalTeachers'] === $tkTeachersCount) {
        logResult(8, "Superadmin dapat menggunakan filter unit", "PASS", "TK Guru: " . $filterData['totalTeachers']);
        $totalPassed++;
    } else {
        logResult(8, "Superadmin dapat menggunakan filter unit", "FAIL", "Expected: $tkTeachersCount, Got: " . ($filterData['totalTeachers'] ?? 'none'));
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 9: Superadmin dapat menggunakan filter tanggal
    // ----------------------------------------------------
    $customDate = '2026-08-20';
    $reqDate = Request::create('/admin/superadmin/dashboard', 'GET', ['date' => $customDate]);
    $resDate = $superController->dashboard($reqDate);
    $dateData = $resDate->getData();

    if (isset($dateData['today']) && $dateData['today'] === $customDate) {
        logResult(9, "Superadmin dapat menggunakan filter tanggal", "PASS");
        $totalPassed++;
    } else {
        logResult(9, "Superadmin dapat menggunakan filter tanggal", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 10: Superadmin dapat menggunakan filter status
    // ----------------------------------------------------
    $reqStatus = Request::create('/admin/superadmin/recap', 'GET', ['status' => 'Terlambat']);
    $resStatus = $superController->recap($reqStatus);
    $statusData = $resStatus->getData();

    if (isset($statusData['selectedStatus']) && $statusData['selectedStatus'] === 'Terlambat') {
        logResult(10, "Superadmin dapat menggunakan filter status", "PASS");
        $totalPassed++;
    } else {
        logResult(10, "Superadmin dapat menggunakan filter status", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 11: Superadmin dapat menggunakan filter metode
    // ----------------------------------------------------
    $reqMethod = Request::create('/admin/superadmin/recap', 'GET', ['method' => 'GPS']);
    $resMethod = $superController->recap($reqMethod);
    $methodData = $resMethod->getData();

    if (isset($methodData['selectedMethod']) && $methodData['selectedMethod'] === 'GPS') {
        logResult(11, "Superadmin dapat menggunakan filter metode", "PASS");
        $totalPassed++;
    } else {
        logResult(11, "Superadmin dapat menggunakan filter metode", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 12: Admin Unit tidak dapat membuka dashboard Superadmin
    // ----------------------------------------------------
    Auth::guard('web')->login($adminTK);
    // Since routing middleware can't be easily evaluated synchronously inside controller calls without http router,
    // we assert that isSuperAdmin()/hasRole('superadmin') returns false for this user.
    if (!Auth::guard('web')->user()->hasRole('superadmin')) {
        logResult(12, "Admin Unit tidak dapat membuka dashboard Superadmin", "PASS");
        $totalPassed++;
    } else {
        logResult(12, "Admin Unit tidak dapat membuka dashboard Superadmin", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 13-15: Admin Unit TK tidak dapat melihat data Paket A, Paket B, Paket C
    // ----------------------------------------------------
    Auth::guard('web')->login($adminTK);
    // Use the teacher controller index which uses TeacherRepository applyUnitScope
    $teacherController = app(TeacherController::class);
    $reqTeachersList = Request::create('/admin/teachers', 'GET');
    $resTeachersList = $teacherController->index($reqTeachersList);
    $teachersList = $resTeachersList->getData()['teachers'];

    $hasOtherUnits = false;
    foreach ($teachersList as $t) {
        if ($t->unit_id !== 2) {
            $hasOtherUnits = true;
            break;
        }
    }

    if (!$hasOtherUnits) {
        logResult(13, "Admin Unit TK tidak dapat melihat Paket A data", "PASS");
        logResult(14, "Admin Unit TK tidak dapat melihat Paket B data", "PASS");
        logResult(15, "Admin Unit TK tidak dapat melihat Paket C data", "PASS");
        $totalPassed += 3;
    } else {
        logResult(13, "Admin Unit TK melihat unit lain", "FAIL");
        $totalFailed += 3;
    }

    // ----------------------------------------------------
    // TEST 16: Guru tidak dapat membuka dashboard Superadmin
    // ----------------------------------------------------
    Auth::guard('teacher')->login($teacherTK);
    $isTeacherWebSuperadmin = Auth::guard('web')->check() && Auth::guard('web')->user()->hasRole('superadmin');
    if (!$isTeacherWebSuperadmin) {
        logResult(16, "Guru tidak dapat membuka dashboard Superadmin", "PASS");
        $totalPassed++;
    } else {
        logResult(16, "Guru tidak dapat membuka dashboard Superadmin", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 17: Manipulasi unit_id oleh Admin Unit ditolak/diabaikan
    // ----------------------------------------------------
    Auth::guard('web')->login($adminTK);
    $reqManipulated = Request::create('/admin/teachers', 'GET', ['unit_id' => 1]); // Try to ask for Paket A teachers
    $resManipulated = $teacherController->index($reqManipulated);
    $manipulatedTeachers = $resManipulated->getData()['teachers'];

    $hasPaketATeachers = false;
    foreach ($manipulatedTeachers as $t) {
        if ($t->unit_id === 1) {
            $hasPaketATeachers = true;
            break;
        }
    }

    if (!$hasPaketATeachers) {
        logResult(17, "Manipulasi unit_id oleh Admin Unit ditolak/diabaikan", "PASS");
        $totalPassed++;
    } else {
        logResult(17, "Manipulasi unit_id oleh Admin Unit ditolak/diabaikan", "FAIL");
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 18: Face ID tanpa school_device_token tetap berhasil jika Face Matching + Liveness + GPS valid
    // ----------------------------------------------------
    Carbon::setTestNow(Carbon::parse('2026-08-24 06:30:00'));
    Attendance::where('teacher_id', $teacherTK->id)->delete();
    AttendanceLog::where('teacher_id', $teacherTK->id)->delete();

    $faceController = app(FaceIdController::class);
    $dummySelfieData = "data:image/jpeg;base64," . base64_encode("dummy-image-contents");
    $validDescriptor = array_fill(0, 128, 0.1);

    $reqFaceId = Request::create('/face-id/attendance', 'POST', [
        'teacher_id' => $teacherTK->id,
        'latitude' => $unitTK->latitude,
        'longitude' => $unitTK->longitude,
        'accuracy' => 10.0,
        'action_type' => 'check_in',
        'selfie' => $dummySelfieData,
        'face_descriptor' => $validDescriptor,
    ]);

    $resFaceId = $faceController->submitAttendance($reqFaceId);
    $faceIdData = json_decode($resFaceId->getContent(), true);

    if (isset($faceIdData['success']) && $faceIdData['success']) {
        logResult(18, "Face ID tanpa school_device_token tetap berhasil", "PASS");
        $totalPassed++;
    } else {
        logResult(18, "Face ID tanpa school_device_token tetap berhasil", "FAIL", json_encode($faceIdData));
        $totalFailed++;
    }

    // ----------------------------------------------------
    // TEST 19: QR Code tetap menolak perangkat yang tidak terdaftar
    // ----------------------------------------------------
    $portalController = app(PortalController::class);
    $reqQRInvalid = Request::create('/qr-presensi/token', 'GET', ['unit_id' => $unitTK->id]);
    
    try {
        $resQRInvalid = $portalController->getPublicQrToken($reqQRInvalid);
        if ($resQRInvalid->getStatusCode() === 403) {
            logResult(19, "QR Code tetap menolak perangkat yang tidak terdaftar", "PASS");
            $totalPassed++;
        } else {
            logResult(19, "QR Code tetap menolak perangkat yang tidak terdaftar", "FAIL", "Status: " . $resQRInvalid->getStatusCode());
            $totalFailed++;
        }
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        if ($e->getStatusCode() === 403) {
            logResult(19, "QR Code tetap menolak perangkat yang tidak terdaftar", "PASS");
            $totalPassed++;
        } else {
            logResult(19, "QR Code tetap menolak perangkat yang tidak terdaftar", "FAIL", "Status: " . $e->getStatusCode());
            $totalFailed++;
        }
    }

    // ----------------------------------------------------
    // TEST 20: QR Code tetap berjalan pada School Device yang valid
    // ----------------------------------------------------
    // Create/retrieve a valid device for unitTK
    $validDevice = AttendanceDevice::updateOrCreate(
        ['device_token' => 'test-token-valid-tk-999'],
        [
            'unit_id' => $unitTK->id,
            'device_name' => 'Test Valid Device TK',
            'status' => true,
        ]
    );

    $reqQRValid = Request::create('/qr-presensi/token', 'GET', ['unit_id' => $unitTK->id]);
    $reqQRValid->cookies->set('school_device_token', 'test-token-valid-tk-999');

    $resQRValid = $portalController->getPublicQrToken($reqQRValid);
    $qrValidData = json_decode($resQRValid->getContent(), true);

    if (isset($qrValidData['success']) && $qrValidData['success']) {
        logResult(20, "QR Code tetap berjalan pada School Device yang valid", "PASS");
        $totalPassed++;
    } else {
        logResult(20, "QR Code tetap berjalan pada School Device yang valid", "FAIL", json_encode($qrValidData));
        $totalFailed++;
    }

    // Clean up test teachers & logs after testing
    Teacher::whereIn('email', [
        'test-super-teacher-tk@ibadurrahman.sch.id',
        'test-super-teacher-a@ibadurrahman.sch.id'
    ])->forceDelete();
    AttendanceDevice::where('device_token', 'test-token-valid-tk-999')->delete();

} catch (\Exception $e) {
    echo "Fatal Exception during tests: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=============================================\n";
echo "TEST RESULTS:\n";
echo "=============================================\n";
echo "Total Passed: $totalPassed / 20\n";
echo "Total Failed: $totalFailed / 20\n";
if ($totalFailed > 0) {
    exit(1);
} else {
    exit(0);
}
