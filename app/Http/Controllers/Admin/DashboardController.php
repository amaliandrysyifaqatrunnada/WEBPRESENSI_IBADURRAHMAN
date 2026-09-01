<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return redirect()->route('admin.superadmin.dashboard');
        }

        if (auth()->user()->hasRole('koordinator')) {
            return redirect()->route('coordinator.dashboard');
        }

        $today = Carbon::today()->toDateString();
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $monthCarbon = Carbon::parse($selectedMonth);
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();
        
        $unitId = auth()->user()->unit_id;

        // Calculate weekly statistics for Diagram Kehadiran Bulanan
        $year = $monthCarbon->year;
        $monthNum = $monthCarbon->month;
        $weeksData = [];
        
        for ($w = 1; $w <= 4; $w++) {
            $startDay = ($w - 1) * 7 + 1;
            $endDay = ($w === 4) ? $monthCarbon->daysInMonth : $w * 7;

            $startDate = Carbon::create($year, $monthNum, $startDay)->startOfDay()->toDateString();
            $endDate = Carbon::create($year, $monthNum, $endDay)->endOfDay()->toDateString();

            $hadirCount = Attendance::where('unit_id', $unitId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'hadir')
                ->count();

            $terlambatCount = Attendance::where('unit_id', $unitId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'terlambat')
                ->count();

            $total = $hadirCount + $terlambatCount;
            
            $weeksData[$w] = [
                'hadir_percent' => $total > 0 ? round(($hadirCount / $total) * 100) : 0,
                'terlambat_percent' => $total > 0 ? round(($terlambatCount / $total) * 100) : 0,
                'hadir_count' => $hadirCount,
                'terlambat_count' => $terlambatCount
            ];
        }

        // 1. Total Teachers & Status Count scoped to unit
        $totalTeachers = Teacher::where('unit_id', $unitId)->count();
        $activeTeachers = Teacher::where('status', 'active')->where('unit_id', $unitId)->count();
        $inactiveTeachers = Teacher::where('status', 'inactive')->where('unit_id', $unitId)->count();

        // 2. Attendance Statuses Today scoped to unit
        $presentToday = Attendance::where('date', $today)
            ->where('unit_id', $unitId)
            ->where('status', 'hadir')
            ->count();

        $lateToday = Attendance::where('date', $today)
            ->where('unit_id', $unitId)
            ->where('status', 'terlambat')
            ->count();

        // Total present count (Hadir + Terlambat)
        $totalPresentToday = $presentToday + $lateToday;

        // 2.2 Attendance Methods Today scoped to unit
        $qrPresentToday = \App\Models\AttendanceLog::where('unit_id', $unitId)
            ->where('type', 'clock_in')
            ->where('method', 'qr')
            ->where('log_status', 'accepted')
            ->whereDate('created_at', $today)
            ->count();

        $gpsPresentToday = \App\Models\AttendanceLog::where('unit_id', $unitId)
            ->where('type', 'clock_in')
            ->where('method', 'gps')
            ->where('log_status', 'accepted')
            ->whereDate('created_at', $today)
            ->count();

        $faceIdPresentToday = \App\Models\AttendanceLog::where('unit_id', $unitId)
            ->where('type', 'clock_in')
            ->where('method', 'face_id')
            ->where('log_status', 'accepted')
            ->whereDate('created_at', $today)
            ->count();

        $manualPresentToday = \App\Models\AttendanceLog::where('unit_id', $unitId)
            ->where('type', 'clock_in')
            ->where('method', 'manual')
            ->where('log_status', 'accepted')
            ->whereDate('created_at', $today)
            ->count();

        // Present Rate of Active Teachers
        $presentRate = $activeTeachers > 0 ? round(($totalPresentToday / $activeTeachers) * 100) : 0;

        // 3. Izin & Sakit Today scoped to unit
        $uAttendancesToday = Attendance::where('date', $today)->where('unit_id', $unitId)->get();
        $uLeavesToday = \App\Models\LeaveRequest::where('status', 'DISETUJUI')
            ->where('unit_id', $unitId)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        $izinToday = $uAttendancesToday->where('status', 'izin')->count() + $uLeavesToday->where('type', 'izin')->count();
        $sakitToday = $uAttendancesToday->where('status', 'sakit')->count() + $uLeavesToday->where('type', 'sakit')->count();

        // 4. Yet to Check-In Today (Belum Presensi) scoped to unit
        $recordedTeacherIds = $uAttendancesToday->pluck('teacher_id')->toArray();
        $leaveTeacherIds = $uLeavesToday->pluck('teacher_id')->toArray();
        $accountedTeacherIds = array_unique(array_merge($recordedTeacherIds, $leaveTeacherIds));

        $notCheckedInToday = Teacher::where('status', 'active')
            ->where('unit_id', $unitId)
            ->whereNotIn('id', $accountedTeacherIds)
            ->count();

        // 5. Late this Month scoped to unit
        $lateThisMonth = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('unit_id', $unitId)
            ->where('status', 'terlambat')
            ->count();

        // 6. Total Reward / Denda
        $totalReward = 'Rp 1.5M';

        return view('admin.dashboard', compact(
            'totalTeachers', 
            'activeTeachers', 
            'inactiveTeachers', 
            'presentToday', 
            'lateToday', 
            'totalPresentToday',
            'izinToday',
            'sakitToday',
            'presentRate', 
            'notCheckedInToday', 
            'lateThisMonth', 
            'totalReward',
            'qrPresentToday',
            'gpsPresentToday',
            'faceIdPresentToday',
            'manualPresentToday',
            'selectedMonth',
            'weeksData'
        ));
    }
}
