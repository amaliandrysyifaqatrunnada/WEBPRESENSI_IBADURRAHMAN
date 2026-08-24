<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SuperadminController extends Controller
{
    /**
     * Display Superadmin aggregate dashboard.
     */
    public function dashboard(Request $request)
    {
        $today = $request->input('date', Carbon::today()->toDateString());
        $selectedUnitId = $request->input('unit_id', 'All'); // 'All' or specific unit ID

        $units = Unit::all();

        // Base Queries
        $teacherQuery = Teacher::query();
        $attendanceQuery = Attendance::where('date', $today);
        $logQuery = AttendanceLog::whereDate('created_at', $today)->where('log_status', 'accepted');

        if ($selectedUnitId !== 'All') {
            $teacherQuery->where('unit_id', $selectedUnitId);
            $attendanceQuery->where('unit_id', $selectedUnitId);
            $logQuery->where('unit_id', $selectedUnitId);
        }

        // 1. Overall stats
        $totalTeachers = $teacherQuery->count();
        $totalTK = Teacher::where('unit_id', 2)->count();
        $totalPaketA = Teacher::where('unit_id', 1)->count();
        $totalPaketB = Teacher::where('unit_id', 3)->count();
        $totalPaketC = Teacher::where('unit_id', 4)->count();

        // 2. Today's stats
        $attendancesToday = $attendanceQuery->get();

        $presentToday = $attendancesToday->where('status', 'hadir')->count();
        $lateToday = $attendancesToday->where('status', 'terlambat')->count();
        $totalPresentToday = $presentToday + $lateToday;

        $izinToday = $attendancesToday->where('status', 'izin')->count();
        $sakitToday = $attendancesToday->where('status', 'sakit')->count();
        $alpaToday = $attendancesToday->where('status', 'alpa')->count();

        // Belum Hadir (active teachers with no attendance record today)
        $activeTeacherIdsQuery = Teacher::where('status', 'active');
        if ($selectedUnitId !== 'All') {
            $activeTeacherIdsQuery->where('unit_id', $selectedUnitId);
        }
        $activeTeacherIds = $activeTeacherIdsQuery->pluck('id')->toArray();
        $recordedTeacherIds = $attendancesToday->pluck('teacher_id')->toArray();
        $notCheckedInToday = count(array_diff($activeTeacherIds, $recordedTeacherIds));

        $earlyCheckoutToday = $attendancesToday->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal'])->count();

        // 3. Attendance Methods Today
        $gpsPresentToday = (clone $logQuery)->where('type', 'clock_in')->where('method', 'gps')->count();
        $qrPresentToday = (clone $logQuery)->where('type', 'clock_in')->where('method', 'qr')->count();
        $faceIdPresentToday = (clone $logQuery)->where('type', 'clock_in')->where('method', 'face_id')->count();

        // 4. Recap per Unit Table data
        $unitSummary = [];
        foreach ($units as $u) {
            $uTeachers = Teacher::where('unit_id', $u->id)->pluck('id')->toArray();
            $uAttendances = Attendance::where('date', $today)->where('unit_id', $u->id)->get();
            
            $uPresent = $uAttendances->where('status', 'hadir')->count();
            $uLate = $uAttendances->where('status', 'terlambat')->count();
            $uIzin = $uAttendances->where('status', 'izin')->count();
            $uSakit = $uAttendances->where('status', 'sakit')->count();
            $uAlpa = $uAttendances->where('status', 'alpa')->count();

            $uActiveTeachers = Teacher::where('status', 'active')->where('unit_id', $u->id)->pluck('id')->toArray();
            $uRecordedTeachers = $uAttendances->pluck('teacher_id')->toArray();
            $uNotCheckedIn = count(array_diff($uActiveTeachers, $uRecordedTeachers));

            $uEarlyCheckout = $uAttendances->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal'])->count();

            $unitSummary[] = [
                'unit' => $u,
                'total_guru' => count($uTeachers),
                'hadir' => $uPresent + $uLate,
                'terlambat' => $uLate,
                'pulang_awal' => $uEarlyCheckout,
                'izin' => $uIzin,
                'sakit' => $uSakit,
                'alpa' => $uAlpa,
                'belum_hadir' => $uNotCheckedIn
            ];
        }

        // Weekly Trends Dataset for Line Chart (aggregated)
        $chartLabels = [];
        $chartPresent = [];
        $chartLate = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($date)->isoFormat('DD MMM');

            $cQuery = Attendance::where('date', $date);
            if ($selectedUnitId !== 'All') {
                $cQuery->where('unit_id', $selectedUnitId);
            }
            $cRecords = $cQuery->get();

            $chartPresent[] = $cRecords->where('status', 'hadir')->count();
            $chartLate[] = $cRecords->where('status', 'terlambat')->count();
        }

        return view('admin.superadmin.dashboard', compact(
            'totalTeachers',
            'totalTK',
            'totalPaketA',
            'totalPaketB',
            'totalPaketC',
            'totalPresentToday',
            'presentToday',
            'lateToday',
            'izinToday',
            'sakitToday',
            'alpaToday',
            'notCheckedInToday',
            'earlyCheckoutToday',
            'gpsPresentToday',
            'qrPresentToday',
            'faceIdPresentToday',
            'units',
            'selectedUnitId',
            'today',
            'unitSummary',
            'chartLabels',
            'chartPresent',
            'chartLate'
        ));
    }

    /**
     * Display filtered global attendances recap table.
     */
    public function recap(Request $request)
    {
        $units = Unit::all();
        
        $selectedUnitId = $request->input('unit_id', 'All');
        $selectedStatus = $request->input('status', 'All');
        $selectedMethod = $request->input('method', 'All');
        $searchName = $request->input('search_name');
        
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = Attendance::with(['teacher', 'logs', 'unit']);

        // 1. Scoping by Unit
        if ($selectedUnitId !== 'All') {
            $query->where('unit_id', $selectedUnitId);
        }

        // 2. Scoping by Date Range
        $query->whereBetween('date', [$startDate, $endDate]);

        // 3. Scoping by Status
        if ($selectedStatus !== 'All') {
            if ($selectedStatus === 'Terlambat') {
                $query->where('status', 'terlambat');
            } elseif ($selectedStatus === 'Hadir') {
                $query->where('status', 'hadir');
            } elseif ($selectedStatus === 'Pulang Awal') {
                $query->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal']);
            } else {
                $query->where('status', strtolower($selectedStatus));
            }
        }

        // 4. Scoping by Method (GPS, QR, Face ID)
        if ($selectedMethod !== 'All') {
            $methodVal = strtolower(str_replace(' ', '_', $selectedMethod));
            $query->whereHas('logs', function ($q) use ($methodVal) {
                $q->where('method', $methodVal);
            });
        }

        // 5. Scoping by Teacher Name / NIP
        if ($searchName) {
            $query->whereHas('teacher', function ($q) use ($searchName) {
                $q->where('name', 'like', "%{$searchName}%")
                  ->orWhere('nip', 'like', "%{$searchName}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.superadmin.recap', compact(
            'attendances',
            'units',
            'selectedUnitId',
            'selectedStatus',
            'selectedMethod',
            'searchName',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export global or filtered report as Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->parseSuperadminFilters($request);
        
        $query = Attendance::with(['teacher', 'logs', 'unit']);
        $this->applyFilters($query, $filters);
        $attendances = $query->orderBy('date', 'desc')->get();

        return Excel::download(new AttendanceExport($attendances), 'Rekap_Kehadiran_Global_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Export global or filtered report as PDF.
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->parseSuperadminFilters($request);

        $query = Attendance::with(['teacher', 'logs', 'unit']);
        $this->applyFilters($query, $filters);
        $attendances = $query->orderBy('date', 'desc')->get();

        // Compile simple summary stats
        $totalPresent = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalLate = $attendances->where('status', 'terlambat')->count();
        $totalReward = $attendances->where('reward', true)->count();
        $totalPulangAwal = $attendances->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal'])->count();
        $totalPenalties = $attendances->sum('penalty');

        $stats = [
            'present' => $totalPresent,
            'late' => $totalLate,
            'reward' => $totalReward,
            'pulang_awal' => $totalPulangAwal,
            'penalties' => $totalPenalties,
        ];

        $unit = null;
        if ($filters['unit_id'] !== 'All') {
            $unit = Unit::find($filters['unit_id']);
        }

        // Convert format to match original PDF
        $pdfFilters = [
            'type' => 'Rentang Tanggal',
            'date' => '-',
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
            'status' => $filters['status'],
            'teacher_id' => $filters['search_name'] ?: 'Semua Guru',
        ];

        $pdf = Pdf::loadView('admin.reports.pdf', compact('attendances', 'stats', 'pdfFilters', 'unit'));
        return $pdf->download('Laporan_Kehadiran_Global_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Helper to parse filter inputs for superadmin.
     */
    protected function parseSuperadminFilters(Request $request): array
    {
        return [
            'unit_id' => $request->input('unit_id', 'All'),
            'status' => $request->input('status', 'All'),
            'method' => $request->input('method', 'All'),
            'search_name' => $request->input('search_name'),
            'start_date' => $request->input('start_date', Carbon::today()->toDateString()),
            'end_date' => $request->input('end_date', Carbon::today()->toDateString()),
        ];
    }

    /**
     * Helper to apply filters to query.
     */
    protected function applyFilters($query, array $filters)
    {
        if ($filters['unit_id'] !== 'All') {
            $query->where('unit_id', $filters['unit_id']);
        }

        $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        if ($filters['status'] !== 'All') {
            if ($filters['status'] === 'Terlambat') {
                $query->where('status', 'terlambat');
            } elseif ($filters['status'] === 'Hadir') {
                $query->where('status', 'hadir');
            } elseif ($filters['status'] === 'Pulang Awal') {
                $query->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal']);
            } else {
                $query->where('status', strtolower($filters['status']));
            }
        }

        if ($filters['method'] !== 'All') {
            $methodVal = strtolower(str_replace(' ', '_', $filters['method']));
            $query->whereHas('logs', function ($q) use ($methodVal) {
                $q->where('method', $methodVal);
            });
        }

        if ($filters['search_name']) {
            $searchName = $filters['search_name'];
            $query->whereHas('teacher', function ($q) use ($searchName) {
                $q->where('name', 'like', "%{$searchName}%")
                  ->orWhere('nip', 'like', "%{$searchName}%");
            });
        }
    }
}
