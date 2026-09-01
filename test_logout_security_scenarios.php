<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "====================================================\n";
echo "TESTING LOGOUT & SESSION EXPIRATION FIX\n";
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

// 1. Test Admin GET Logout
$superadminUser = User::where('email', 'superadmin@ibadurrahman.sch.id')->first();
Auth::guard('web')->login($superadminUser);

$requestGet = Request::create('/admin/logout', 'GET');
$responseGet = $app->handle($requestGet);
runTest("Test 01: Admin GET /admin/logout redirect ke portal", $responseGet->isRedirect(route('portal')), "GET /admin/logout berhasil logout dan redirect ke portal");

// 2. Test Admin POST Logout
Auth::guard('web')->login($superadminUser);
$requestPost = Request::create('/admin/logout', 'POST');
$responsePost = $app->handle($requestPost);
$statusCode = $responsePost->getStatusCode();
runTest("Test 02: Admin POST /admin/logout redirect ke portal", in_array($statusCode, [302, 303]), "POST /admin/logout status code {$statusCode} redirect ke portal");

// 3. Test Teacher GET Logout
$teacher = Teacher::first();
Auth::guard('teacher')->login($teacher);

$requestTeacherGet = Request::create('/teacher/logout', 'GET');
$responseTeacherGet = $app->handle($requestTeacherGet);
runTest("Test 03: Teacher GET /teacher/logout redirect ke portal", $responseTeacherGet->isRedirect(route('portal')), "GET /teacher/logout berhasil logout dan redirect ke portal");

echo "\n====================================================\n";
echo "LOGOUT TEST SUMMARY: {$passed} / 3 PASSED\n";
echo "====================================================\n";

exit($failed > 0 ? 1 : 0);
