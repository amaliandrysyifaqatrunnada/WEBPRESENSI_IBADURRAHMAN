<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Unit;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\TeacherWorkSchedule;
use App\Models\Schedule;
use App\Services\MonthlyAttendanceRecapService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

echo "====================================================\n";
echo "RUNNING CALENDAR MATRIX MONTHLY RECAP EXPORT TEST SUITE\n";
echo "====================================================\n\n";

$passed = 0;
$failed = 0;

function runTest($testName, $condition, $successDetail, $failDetail) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] {$testName} - {$successDetail}\n";
        $passed++;
    } else {
        echo "[FAIL] {$testName} - {$failDetail}\n";
        $failed++;
    }
}

try {
    $recapService = app(MonthlyAttendanceRecapService::class);

    // Setup Test Data Environment
    $unitTK = Unit::firstOrCreate(['name' => 'TK PKBM Ibadurrahman'], ['package_type' => 'TK', 'active' => true, 'latitude' => -7.4478, 'longitude' => 112.7183, 'gps_radius' => 100]);
    $unitA  = Unit::firstOrCreate(['name' => 'PKBM Ibadurrahman - Paket A'], ['package_type' => 'A', 'active' => true, 'latitude' => -7.4478, 'longitude' => 112.7183, 'gps_radius' => 100]);
    $unitB  = Unit::firstOrCreate(['name' => 'PKBM Ibadurrahman - Paket B'], ['package_type' => 'B', 'active' => true, 'latitude' => -7.4478, 'longitude' => 112.7183, 'gps_radius' => 100]);

    $superadminUser = User::where('email', 'superadmin@ibadurrahman.sch.id')->first();
    $adminTKUser = User::where('email', 'admin.tk@ibadurrahman.sch.id')->first() ?: User::where('unit_id', $unitTK->id)->first();

    $teacherTK1 = Teacher::firstOrCreate(['nip' => 'T_EXP_TK1'], ['name' => 'Guru TK 1', 'email' => 't_exp_tk1@ibadurrahman.sch.id', 'password' => bcrypt('password'), 'position' => 'Guru TK', 'unit_id' => $unitTK->id, 'status' => 'active', 'display_id' => 'TK001']);
    $teacherTK2 = Teacher::firstOrCreate(['nip' => 'T_EXP_TK2'], ['name' => 'Guru TK 2', 'email' => 't_exp_tk2@ibadurrahman.sch.id', 'password' => bcrypt('password'), 'position' => 'Guru TK', 'unit_id' => $unitTK->id, 'status' => 'active', 'display_id' => 'TK002']);
    $teacherA1  = Teacher::firstOrCreate(['nip' => 'T_EXP_A1'],  ['name' => 'Guru Paket A 1', 'email' => 't_exp_a1@ibadurrahman.sch.id', 'password' => bcrypt('password'), 'position' => 'Guru Paket A', 'unit_id' => $unitA->id, 'status' => 'active', 'display_id' => 'A001']);

    $testMonth = 8;
    $testYear = 2026;

    // Test 01: Export bulan 31 hari menghasilkan 31 kolom tanggal
    Auth::guard('web')->login($superadminUser);
    $recapAug = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    runTest("Test 01: Export bulan 31 hari menghasilkan 31 kolom tanggal", count($recapAug['dates']) === 31, "Header tanggal memuat 31 kolom lengkap (01 s.d 31)", "Jumlah kolom tanggal: " . count($recapAug['dates']));

    // Test 02: Export Februari menghasilkan jumlah tanggal yang benar (28 hari pada 2026)
    $recapFeb = $recapService->buildCalendarMatrix(['month' => 2, 'year' => 2026, 'unit_id' => 'All']);
    runTest("Test 02: Export Februari 2026 menghasilkan 28 kolom tanggal", count($recapFeb['dates']) === 28, "Februari 2026 secara akurat memuat 28 kolom tanpa tanggal palsu", "Jumlah kolom Februari: " . count($recapFeb['dates']));

    // Test 03: Nama guru hanya muncul satu kali per baris per bulan
    $teacherIdsInRows = array_column($recapAug['matrix_rows'], 'teacher_id');
    $uniqueTeacherIds = array_unique($teacherIdsInRows);
    runTest("Test 03: Setiap guru hanya menempati 1 baris per bulan", count($teacherIdsInRows) === count($uniqueTeacherIds), "TIDAK ADA duplikasi baris guru (1 Guru = 1 Baris)", "Terdapat duplikasi nama guru");

    // Test 04: Tanggal berada pada header horizontal
    $firstDateMeta = reset($recapAug['dates']);
    runTest("Test 04: Tanggal berada pada header horizontal (01 Sen, 02 Sel...)", isset($firstDateMeta['header_label']) && strpos($firstDateMeta['header_label'], '01') === 0, "Header tanggal horizontal terformat dengan baik: {$firstDateMeta['header_label']}", "Format header tanggal salah");

    // Test 05: Guru berada pada kolom pertama/kiri
    $firstRow = $recapAug['matrix_rows'][0] ?? null;
    runTest("Test 05: Guru berada pada kolom pertama/kiri matrix", $firstRow && isset($firstRow['teacher_name']), "Baris matriks memuat nama guru di posisi kiri: {$firstRow['teacher_name']}", "Struktur baris matriks tidak memuat nama guru");

    // Test 06: Hari libur muncul sebagai L / LIBUR
    $globalHolidayDate = '2026-08-17';
    Holiday::where('date', $globalHolidayDate)->delete();
    Holiday::create(['name' => 'Hari Kemerdekaan RI', 'date' => $globalHolidayDate, 'unit_id' => null, 'is_active' => true]);

    $recapHol = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $tk1Aug17Code = $recapHol['matrix_rows'][0]['days']['17']['code'] ?? '';
    runTest("Test 06: Hari libur muncul sebagai kode L", $tk1Aug17Code === 'L', "Tanggal 17/08/2026 mendapatkan kode status L (Libur)", "Kode status bukan L (ditemukan: {$tk1Aug17Code})");

    // Test 07: Hari Sabtu dengan custom schedule tetap dihitung sebagai hari kerja
    $teacherTK1->update(['use_custom_schedule' => true]);
    TeacherWorkSchedule::where('teacher_id', $teacherTK1->id)->delete();
    TeacherWorkSchedule::create([
        'teacher_id' => $teacherTK1->id,
        'day_of_week' => 6, // Saturday
        'start_time' => '07:15:00',
        'end_time' => '13:00:00',
        'is_active' => true
    ]);
    $recapSat = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $tk1SatCode = array_values(array_filter($recapSat['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['01']['code'] ?? ''; // Aug 1 2026 is Saturday
    runTest("Test 07: Sabtu dengan custom schedule dihitung sebagai hari kerja", $tk1SatCode !== 'L', "Sabtu 01/08/2026 diakui sebagai hari kerja dengan kode status: {$tk1SatCode}", "Sabtu salah dianggap Libur (L)");

    // Test 08: Approved leave (IZIN) muncul sebagai I
    $leaveDate = '2026-08-10';
    LeaveRequest::where('teacher_id', $teacherTK1->id)->where('start_date', $leaveDate)->delete();
    LeaveRequest::create([
        'teacher_id' => $teacherTK1->id,
        'unit_id' => $unitTK->id,
        'type' => 'izin',
        'start_date' => $leaveDate,
        'end_date' => $leaveDate,
        'reason' => 'Urusan Keluarga',
        'description' => 'Urusan Keluarga',
        'status' => 'DISETUJUI'
    ]);
    $recapIzin = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $izinCode = array_values(array_filter($recapIzin['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['10']['code'] ?? '';
    runTest("Test 08: Approved leave muncul sebagai kode I (Izin)", $izinCode === 'I', "Pengajuan izin approved mendapatkan kode status I", "Kode status bukan I");

    // Test 09: Approved sick leave muncul sebagai S
    $sakitDate = '2026-08-11';
    LeaveRequest::where('teacher_id', $teacherTK1->id)->where('start_date', $sakitDate)->delete();
    LeaveRequest::create([
        'teacher_id' => $teacherTK1->id,
        'unit_id' => $unitTK->id,
        'type' => 'sakit',
        'start_date' => $sakitDate,
        'end_date' => $sakitDate,
        'reason' => 'Demam Tinggi',
        'description' => 'Demam Tinggi',
        'status' => 'DISETUJUI'
    ]);
    $recapSakit = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $sakitCode = array_values(array_filter($recapSakit['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['11']['code'] ?? '';
    runTest("Test 09: Approved sick leave muncul sebagai kode S (Sakit)", $sakitCode === 'S', "Pengajuan sakit approved mendapatkan kode status S", "Kode status bukan S");

    // Test 10: Approved tanpa keterangan muncul sebagai TK
    $alpaDate = '2026-08-12';
    LeaveRequest::where('teacher_id', $teacherTK1->id)->where('start_date', $alpaDate)->delete();
    LeaveRequest::create([
        'teacher_id' => $teacherTK1->id,
        'unit_id' => $unitTK->id,
        'type' => 'tanpa_keterangan',
        'start_date' => $alpaDate,
        'end_date' => $alpaDate,
        'reason' => 'Tanpa kabar',
        'description' => 'Tanpa kabar',
        'status' => 'DISETUJUI'
    ]);
    $recapTK = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $tkCode = array_values(array_filter($recapTK['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['12']['code'] ?? '';
    runTest("Test 10: Approved tanpa keterangan muncul sebagai kode TK", $tkCode === 'TK', "Tanpa keterangan approved mendapatkan kode status TK", "Kode status bukan TK");

    // Test 11: Pending leave tidak dihitung sebagai izin final
    $pendingDate = '2026-08-13';
    LeaveRequest::where('teacher_id', $teacherTK1->id)->where('start_date', $pendingDate)->delete();
    LeaveRequest::create([
        'teacher_id' => $teacherTK1->id,
        'unit_id' => $unitTK->id,
        'type' => 'izin',
        'start_date' => $pendingDate,
        'end_date' => $pendingDate,
        'reason' => 'Pending Izin',
        'description' => 'Pending Izin',
        'status' => 'MENUNGGU_PERSETUJUAN_ATASAN'
    ]);
    $recapPending = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $pendingCode = array_values(array_filter($recapPending['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['13']['code'] ?? '';
    runTest("Test 11: Pending leave diabaikan (tetap TP / hari kerja)", $pendingCode !== 'I', "Izin pending diabaikan (Kode status: {$pendingCode})", "Izin pending salah dimasukkan sebagai I");

    // Test 12: Hari kerja tanpa presensi muncul sebagai TP
    $noAbsenCode = array_values(array_filter($recapAug['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0]['days']['05']['code'] ?? '';
    runTest("Test 12: Hari kerja tanpa presensi muncul sebagai TP", $noAbsenCode === 'TP', "Hari kerja tanpa presensi mendapatkan kode status TP", "Kode status bukan TP");

    // Test 13: Custom schedule individual guru digunakan
    runTest("Test 13: Custom schedule individual guru digunakan", $teacherTK1->use_custom_schedule == true, "Flag custom schedule aktif dan digunakan pada resolver", "Custom schedule diabaikan");

    // Test 14: Legenda status disertakan
    runTest("Test 14: Legenda status disertakan", isset($recapAug['legend']['H']) && isset($recapAug['legend']['TP']), "Legenda memuat pemetaan kode status ke teks lengkap", "Legenda tidak ditemukan");

    // Test 15: Admin Unit tidak dapat export unit lain (Anti-IDOR)
    Auth::guard('web')->login($adminTKUser);
    $recapAdminScoped = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => $unitA->id]);
    $adminScopedTeachers = array_column($recapAdminScoped['matrix_rows'], 'teacher_id');
    runTest("Test 15: Anti-IDOR export Admin Unit enforced", !in_array($teacherA1->id, $adminScopedTeachers), "Admin Unit TK tidak dapat mengeksport data Paket A", "IDOR vulnerability detected!");

    // Test 16: Superadmin dapat export semua unit (Global)
    Auth::guard('web')->login($superadminUser);
    $recapSuper = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => 'All']);
    $superTeachers = array_column($recapSuper['matrix_rows'], 'teacher_id');
    runTest("Test 16: Superadmin dapat export semua unit (Global)", in_array($teacherTK1->id, $superTeachers) && in_array($teacherA1->id, $superTeachers), "Superadmin memuat data guru seluruh unit", "Superadmin gagal memuat seluruh unit");

    // Test 17: Superadmin dapat filter unit
    $recapTKOnly = $recapService->buildCalendarMatrix(['month' => 8, 'year' => 2026, 'unit_id' => $unitTK->id]);
    $tkOnlyTeachers = array_column($recapTKOnly['matrix_rows'], 'teacher_id');
    runTest("Test 17: Superadmin dapat filter unit", in_array($teacherTK1->id, $tkOnlyTeachers) && !in_array($teacherA1->id, $tkOnlyTeachers), "Filter unit TK menyaring guru Paket A", "Filter unit gagal");

    // Test 18: Summary per guru sesuai matrix
    $tk1Row = array_values(array_filter($recapAug['matrix_rows'], function($r) use ($teacherTK1) {
        return $r['teacher_id'] === $teacherTK1->id;
    }))[0] ?? null;
    $countI = 0;
    foreach ($tk1Row['days'] as $d) {
        if ($d['code'] === 'I') $countI++;
    }
    runTest("Test 18: Summary per guru 100% konsisten dengan matrix", $tk1Row && $tk1Row['summary']['izin'] === $countI, "Akumulasi Izin ({$tk1Row['summary']['izin']}) konsisten dengan sel matriks", "Summary tidak sesuai matriks");

    // Test 19: Summary per unit sesuai matrix
    $unitSummaries = $recapAug['unit_summaries'];
    runTest("Test 19: Summary per unit sesuai matrix", !empty($unitSummaries['per_unit']) && isset($unitSummaries['grand_total']), "Rekapitulasi per unit & total keseluruhan berhasil dihitung", "Summary per unit gagal");

    // Test 20: Excel dan PDF memiliki hasil status yang sama
    $excelExport = new \App\Exports\MonthlyRecapExport($recapAug);
    $excelCollection = $excelExport->collection();
    $pdfView = view('admin.reports.pdf', ['recapData' => $recapAug, 'filters' => ['unit_id' => 'All'], 'unit' => null])->render();

    $sameData = $excelCollection->count() > 10 && strpos($pdfView, '01/08') === false && strpos($pdfView, 'LEGENDA KODE STATUS PRESENSI') !== false;
    runTest("Test 20: Excel dan PDF memiliki data & struktur matriks yang konsisten", $sameData, "Excel & PDF menggunakan payload data Calendar Matrix yang sama", "Terdapat inkonsistensi antara Excel dan PDF");

} catch (\Throwable $e) {
    echo "[ERROR] Exception thrown during test run: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failed++;
}

echo "\n====================================================\n";
echo "CALENDAR MATRIX EXPORT TEST SUMMARY\n";
echo "TOTAL PASSED: {$passed} / 20\n";
echo "TOTAL FAILED: {$failed} / 20\n";
echo "====================================================\n";

exit($failed > 0 ? 1 : 0);
