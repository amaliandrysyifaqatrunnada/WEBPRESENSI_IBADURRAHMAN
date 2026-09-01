<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Teacher;
use App\Models\TeacherWorkSchedule;
use App\Models\Schedule;
use App\Models\Unit;
use Carbon\Carbon;

class MonthlyAttendanceRecapService
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Build full 2D Calendar Matrix for Monthly Attendance Recap.
     * Horizontal: Dates (01, 02, ..., 28/29/30/31)
     * Vertical: Active Teachers (1 row per teacher per month)
     */
    public function buildCalendarMatrix(array $filters): array
    {
        // 1. Authorization & Unit Scoping
        $user = auth()->guard('web')->user();
        $isSuperadmin = $user && $user->hasRole('superadmin');

        if (!$isSuperadmin) {
            $selectedUnitId = $user ? $user->unit_id : null;
        } else {
            $selectedUnitId = $filters['unit_id'] ?? 'All';
        }

        // 2. Date Range & Month Days Determination
        $year = (int) ($filters['year'] ?? Carbon::now()->year);
        $month = (int) ($filters['month'] ?? Carbon::now()->month);

        $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $lastDayOfMonth = Carbon::create($year, $month, $daysInMonth)->endOfDay();

        $startDateStr = $firstDayOfMonth->toDateString();
        $endDateStr = $lastDayOfMonth->toDateString();

        // 3. Build Header Dates Array
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cDate = Carbon::create($year, $month, $d);
            $dayNumStr = sprintf('%02d', $d);
            $dayShortName = $cDate->locale('id')->isoFormat('dd'); // Sen, Sel, Rab, Kam, Jum, Sab, Min
            
            $dates[$dayNumStr] = [
                'day_num' => $dayNumStr,
                'date_str' => $cDate->toDateString(),
                'day_short' => ucfirst($dayShortName),
                'header_label' => $dayNumStr . ' ' . ucfirst($dayShortName),
                'is_sunday' => ($cDate->dayOfWeekIso === 7),
                'is_saturday' => ($cDate->dayOfWeekIso === 6),
            ];
        }

        // 4. Fetch Active Teachers in Scope
        $teacherQuery = Teacher::with('unit')->where('status', 'active');
        if ($selectedUnitId && $selectedUnitId !== 'All') {
            $teacherQuery->where('unit_id', $selectedUnitId);
        }
        if (!empty($filters['search_name']) && !in_array($filters['search_name'], ['Semua Guru', 'All Teachers'])) {
            $search = $filters['search_name'];
            $teacherQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['teacher_id']) && is_numeric($filters['teacher_id'])) {
            $teacherQuery->where('id', $filters['teacher_id']);
        }

        $teachers = $teacherQuery->orderBy('unit_id', 'asc')->orderBy('name', 'asc')->get();

        // 5. Pre-fetch Data for Month Range
        $attendanceQuery = Attendance::whereBetween('date', [$startDateStr, $endDateStr]);
        if ($selectedUnitId && $selectedUnitId !== 'All') {
            $attendanceQuery->where('unit_id', $selectedUnitId);
        }
        $allAttendances = $attendanceQuery->get()->groupBy(function ($att) {
            return $att->date . '_' . $att->teacher_id;
        });

        $allHolidays = Holiday::where('is_active', true)
            ->whereDate('date', '>=', $startDateStr)
            ->whereDate('date', '<=', $endDateStr)
            ->get();

        $allLeaves = LeaveRequest::where('status', 'DISETUJUI')
            ->whereDate('start_date', '<=', $endDateStr)
            ->whereDate('end_date', '>=', $startDateStr)
            ->get();

        // 6. Build Matrix Rows (1 Row Per Teacher)
        $matrixRows = [];
        $teacherSummaries = [];

        foreach ($teachers as $teacher) {
            $unitId = $teacher->unit_id;
            $unitName = $teacher->unit ? $teacher->unit->name : '-';

            $rowDays = [];
            $summary = [
                'teacher' => $teacher,
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'teacher_nip' => $teacher->nip ?? '-',
                'teacher_display_id' => $teacher->display_id,
                'unit_id' => $unitId,
                'unit_name' => $unitName,
                'hadir' => 0,
                'terlambat' => 0,
                'pulang_awal' => 0,
                'izin' => 0,
                'sakit' => 0,
                'tanpa_ket' => 0,
                'libur' => 0,
                'tanpa_presensi' => 0,
            ];

            foreach ($dates as $dayNum => $dateMeta) {
                $dateStr = $dateMeta['date_str'];
                $cDate = Carbon::parse($dateStr);
                $isoDayOfWeek = $cDate->dayOfWeekIso;

                // A. Priority 1: Active Holiday Check
                $holiday = $allHolidays->first(function ($h) use ($dateStr, $unitId) {
                    $hDate = $h->date instanceof Carbon ? $h->date->toDateString() : Carbon::parse($h->date)->toDateString();
                    return $hDate === $dateStr && ($h->unit_id === null || $h->unit_id == $unitId);
                });

                // B. Priority 2: Non-Working Day (Schedule Check)
                $scheduleInfo = $this->attendanceService->getEffectiveWorkSchedule($teacher, $dateStr);
                $isWeekend = ($isoDayOfWeek === 7); // Sunday
                $isNonWorkDay = false;
                if ($isWeekend) {
                    if ($teacher->use_custom_schedule) {
                        $customSun = TeacherWorkSchedule::where('teacher_id', $teacher->id)->where('day_of_week', 7)->where('is_active', true)->first();
                        if (!$customSun) {
                            $isNonWorkDay = true;
                        }
                    } else {
                        $unitSun = Schedule::where('unit_id', $teacher->unit_id)->where('day_of_week', 'sunday')->where('is_active', true)->first();
                        if (!$unitSun) {
                            $isNonWorkDay = true;
                        }
                    }
                }

                // C. Priority 3: Approved Leave Check (DISETUJUI Status Only)
                $approvedLeave = $allLeaves->first(function ($l) use ($teacher, $dateStr) {
                    $sDate = $l->start_date instanceof Carbon ? $l->start_date->toDateString() : Carbon::parse($l->start_date)->toDateString();
                    $eDate = $l->end_date instanceof Carbon ? $l->end_date->toDateString() : Carbon::parse($l->end_date)->toDateString();
                    return $l->teacher_id == $teacher->id && $sDate <= $dateStr && $eDate >= $dateStr;
                });

                // D. Priority 4: Attendance Record
                $key = $dateStr . '_' . $teacher->id;
                $attendance = isset($allAttendances[$key]) ? $allAttendances[$key]->first() : null;

                // E. Resolve Cell Status Code, Details & Scan Times
                $jamMasuk = ($attendance && $attendance->clock_in) ? substr($attendance->clock_in, 0, 5) : '-';
                $jamPulang = ($attendance && $attendance->clock_out) ? substr($attendance->clock_out, 0, 5) : '-';
                $metode = ($attendance && $attendance->check_in_method) ? strtoupper($attendance->check_in_method) : '-';

                if ($holiday) {
                    $code = 'L';
                    $label = 'LIBUR';
                    $keterangan = $holiday->name;
                    $scanDisplay = 'LIBUR';
                } elseif ($isNonWorkDay) {
                    $code = 'L';
                    $label = 'LIBUR';
                    $keterangan = 'Hari Libur (Minggu)';
                    $scanDisplay = 'LIBUR';
                } elseif ($approvedLeave) {
                    if ($approvedLeave->type === 'sakit') {
                        $code = 'S';
                        $label = 'SAKIT';
                        $scanDisplay = 'Sakit';
                    } elseif ($approvedLeave->type === 'tanpa_keterangan') {
                        $code = 'TK';
                        $label = 'TANPA KETERANGAN';
                        $scanDisplay = 'TK';
                    } else {
                        $code = 'I';
                        $label = 'IZIN';
                        $scanDisplay = 'Izin';
                    }
                    $keterangan = $approvedLeave->description ?: ($approvedLeave->notes ?: ucfirst($approvedLeave->type));
                } elseif ($attendance) {
                    if (in_array(strtolower($attendance->status), ['izin', 'sakit', 'alpa'])) {
                        if ($attendance->status === 'sakit') {
                            $code = 'S';
                            $label = 'SAKIT';
                            $scanDisplay = 'Sakit';
                        } elseif ($attendance->status === 'alpa') {
                            $code = 'TK';
                            $label = 'TANPA KETERANGAN';
                            $scanDisplay = 'TK';
                        } else {
                            $code = 'I';
                            $label = 'IZIN';
                            $scanDisplay = 'Izin';
                        }
                        $keterangan = ucfirst($attendance->status);
                    } elseif ($attendance->status === 'terlambat' || $attendance->status_masuk === 'Terlambat') {
                        $code = 'TL';
                        $label = 'TERLAMBAT';
                        $keterangan = 'Terlambat';
                        $scanDisplay = "{$jamMasuk}\n{$jamPulang}";
                    } elseif ($attendance->status_pulang && in_array($attendance->status_pulang, ['Pulang Awal', 'Pulang Lebih Awal'])) {
                        $code = 'PPA';
                        $label = 'PULANG LEBIH AWAL';
                        $keterangan = 'Pulang Lebih Awal';
                        $scanDisplay = "{$jamMasuk}\n{$jamPulang}";
                    } else {
                        $code = 'H';
                        $label = 'HADIR';
                        $keterangan = '-';
                        $scanDisplay = "{$jamMasuk}\n{$jamPulang}";
                    }
                } else {
                    $code = 'TP';
                    $label = 'TANPA PRESENSI';
                    $keterangan = '-';
                    $scanDisplay = 'TP';
                }

                // Update Teacher Summary Counter
                if ($code === 'H') $summary['hadir']++;
                elseif ($code === 'TL') $summary['terlambat']++;
                elseif ($code === 'PPA') $summary['pulang_awal']++;
                elseif ($code === 'I') $summary['izin']++;
                elseif ($code === 'S') $summary['sakit']++;
                elseif ($code === 'TK') $summary['tanpa_ket']++;
                elseif ($code === 'L') $summary['libur']++;
                elseif ($code === 'TP') $summary['tanpa_presensi']++;

                $rowDays[$dayNum] = [
                    'code' => $code,
                    'label' => $label,
                    'keterangan' => $keterangan,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'scan_display' => $scanDisplay,
                    'metode' => $metode,
                ];
            }

            $matrixRows[] = [
                'teacher' => $teacher,
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'teacher_nip' => $teacher->nip ?? '-',
                'teacher_display_id' => $teacher->display_id,
                'unit_id' => $unitId,
                'unit_name' => $unitName,
                'days' => $rowDays,
                'summary' => $summary,
            ];

            $teacherSummaries[] = $summary;
        }

        // 7. Build Detail Presensi Data Array (Ordered by Date ASC -> Unit ASC -> Teacher Name ASC)
        $detailPresensi = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayNumStr = sprintf('%02d', $d);
            $dateMeta = $dates[$dayNumStr];
            $dateStr = $dateMeta['date_str'];
            $cDate = Carbon::parse($dateStr);
            $dayNameIndo = ucfirst($cDate->locale('id')->isoFormat('dddd'));

            foreach ($matrixRows as $mRow) {
                $dayCell = $mRow['days'][$dayNumStr] ?? null;
                if ($dayCell) {
                    $detailPresensi[] = [
                        'date' => Carbon::parse($dateStr)->format('d-m-Y'),
                        'date_raw' => $dateStr,
                        'day_name' => $dayNameIndo,
                        'teacher_name' => $mRow['teacher_name'],
                        'unit_name' => $mRow['unit_name'],
                        'status' => $dayCell['label'],
                        'code' => $dayCell['code'],
                        'jam_masuk' => $dayCell['jam_masuk'],
                        'jam_pulang' => $dayCell['jam_pulang'],
                        'metode' => $dayCell['metode'],
                        'keterangan' => $dayCell['keterangan'],
                    ];
                }
            }
        }

        // 8. Build Unit Summaries (For Superadmin & Global View)
        $unitSummaries = $this->buildUnitSummary($teacherSummaries);

        // 9. Determine Unit Header Label
        $unitLabel = 'Semua Unit';
        if ($selectedUnitId && $selectedUnitId !== 'All') {
            $uObj = Unit::find($selectedUnitId);
            $unitLabel = $uObj ? $uObj->name : 'Unit ' . $selectedUnitId;
        }

        $periodLabel = $firstDayOfMonth->locale('id')->isoFormat('MMMM YYYY');

        $legend = [
            'H' => 'Hadir (Tepat Waktu)',
            'TL' => 'Terlambat',
            'PPA' => 'Pulang Lebih Awal',
            'I' => 'Izin',
            'S' => 'Sakit',
            'TK' => 'Tanpa Keterangan',
            'L' => 'Libur',
            'TP' => 'Tanpa Presensi',
        ];

        return [
            'year' => $year,
            'month' => $month,
            'period_label' => $periodLabel,
            'unit_label' => $unitLabel,
            'selected_unit_id' => $selectedUnitId,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'days_in_month' => $daysInMonth,
            'dates' => $dates,
            'matrix_rows' => $matrixRows,
            'detail_presensi' => $detailPresensi,
            'teacher_summaries' => $teacherSummaries,
            'unit_summaries' => $unitSummaries,
            'legend' => $legend,
        ];
    }

    /**
     * Build aggregated totals grouped by Unit plus Total Keseluruhan.
     */
    public function buildUnitSummary(array $teacherSummaries): array
    {
        $grouped = [];

        foreach ($teacherSummaries as $ts) {
            $unitName = $ts['unit_name'];
            if (!isset($grouped[$unitName])) {
                $grouped[$unitName] = [
                    'unit_name' => $unitName,
                    'teacher_count' => 0,
                    'hadir' => 0,
                    'terlambat' => 0,
                    'pulang_awal' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'tanpa_ket' => 0,
                    'libur' => 0,
                    'tanpa_presensi' => 0,
                ];
            }

            $grouped[$unitName]['teacher_count']++;
            $grouped[$unitName]['hadir'] += $ts['hadir'];
            $grouped[$unitName]['terlambat'] += $ts['terlambat'];
            $grouped[$unitName]['pulang_awal'] += $ts['pulang_awal'];
            $grouped[$unitName]['izin'] += $ts['izin'];
            $grouped[$unitName]['sakit'] += $ts['sakit'];
            $grouped[$unitName]['tanpa_ket'] += $ts['tanpa_ket'];
            $grouped[$unitName]['libur'] += $ts['libur'];
            $grouped[$unitName]['tanpa_presensi'] += $ts['tanpa_presensi'];
        }

        $unitsList = array_values($grouped);

        // Total Keseluruhan
        $grandTotal = [
            'unit_name' => 'TOTAL KESELURUHAN',
            'teacher_count' => array_sum(array_column($unitsList, 'teacher_count')),
            'hadir' => array_sum(array_column($unitsList, 'hadir')),
            'terlambat' => array_sum(array_column($unitsList, 'terlambat')),
            'pulang_awal' => array_sum(array_column($unitsList, 'pulang_awal')),
            'izin' => array_sum(array_column($unitsList, 'izin')),
            'sakit' => array_sum(array_column($unitsList, 'sakit')),
            'tanpa_ket' => array_sum(array_column($unitsList, 'tanpa_ket')),
            'libur' => array_sum(array_column($unitsList, 'libur')),
            'tanpa_presensi' => array_sum(array_column($unitsList, 'tanpa_presensi')),
        ];

        return [
            'per_unit' => $unitsList,
            'grand_total' => $grandTotal,
        ];
    }
}
