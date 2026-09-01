<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Helper to apply unit scoping to any query builder.
     */
    protected function applyUnitScope($query)
    {
        if (auth()->guard('web')->check()) {
            $unitId = auth()->guard('web')->user()->unit_id;
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
        }
        return $query;
    }

    /**
     * Get filtered attendance records.
     */
    public function getFilteredAttendances(array $filters)
    {
        $query = Attendance::with(['teacher', 'logs']);
        $this->applyUnitScope($query);
        $this->applyFilters($query, $filters);

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Get paginated filtered attendance records.
     */
    public function getFilteredAttendancesPaginated(array $filters, int $perPage = 15)
    {
        $query = Attendance::with(['teacher', 'logs']);
        $this->applyUnitScope($query);
        $this->applyFilters($query, $filters);

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    /**
     * Calculate summary statistics for filtered records.
     */
    public function getReportStats(array $filters): array
    {
        $query = Attendance::query();
        $this->applyUnitScope($query);
        $this->applyFilters($query, $filters);

        $records = $query->get();

        // Calculate based on new terperinci status
        // status_masuk can be 'Tepat Waktu' or 'Terlambat'
        // status_pulang can be 'Normal' or 'Pulang Awal'
        // status column is retained for compatibility ('hadir', 'terlambat', 'izin', 'sakit', 'alpa')
        $totalPresent = $records->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalLate = $records->where('status', 'terlambat')->count();
        $totalReward = $records->where('reward', true)->count();
        $totalPulangAwal = $records->whereIn('status_pulang', ['Pulang Awal', 'Pulang Lebih Awal'])->count();
        $totalIzin = $records->where('status', 'izin')->count();
        $totalSakit = $records->where('status', 'sakit')->count();
        $totalAlpa = $records->where('status', 'alpa')->count();
        $totalPenalties = $records->sum('penalty');

        return [
            'present' => $totalPresent,
            'late' => $totalLate,
            'reward' => $totalReward,
            'pulang_awal' => $totalPulangAwal,
            'izin' => $totalIzin,
            'sakit' => $totalSakit,
            'alpa' => $totalAlpa,
            'penalties' => $totalPenalties,
        ];
    }

    /**
     * Compile presence datasets for Chart.js trend rendering.
     */
    public function getChartData(array $filters): array
    {
        $labels = [];
        $presentDataset = [];
        $lateDataset = [];

        $type = $filters['type'] ?? 'bulanan';
        
        if ($type === 'harian' || $type === 'mingguan') {
            // Trend of last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->toDateString();
                $labels[] = Carbon::parse($date)->isoFormat('DD MMM');

                $query = Attendance::where('date', $date);
                $this->applyUnitScope($query);
                $records = $query->get();

                $presentDataset[] = $records->whereIn('status', ['hadir', 'terlambat'])->count();
                $lateDataset[] = $records->where('status', 'terlambat')->count();
            }
        } else {
            // Trend of last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $monthObj = Carbon::now()->subMonths($i);
                $labels[] = $monthObj->isoFormat('MMMM');

                $query = Attendance::whereYear('date', $monthObj->year)
                    ->whereMonth('date', $monthObj->month);
                $this->applyUnitScope($query);
                $records = $query->get();

                $presentDataset[] = $records->whereIn('status', ['hadir', 'terlambat'])->count();
                $lateDataset[] = $records->where('status', 'terlambat')->count();
            }
        }

        return [
            'labels' => $labels,
            'present' => $presentDataset,
            'late' => $lateDataset,
        ];
    }

    /**
     * Apply filter parameters to the query builder.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        // Enforce that attendance records must belong to an active (non-deleted) teacher
        $query->whereHas('teacher');

        // 1. Filter by Teacher
        if (!empty($filters['teacher_id']) && $filters['teacher_id'] !== 'All Teachers') {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        // 2. Filter by Status
        if (!empty($filters['status']) && $filters['status'] !== 'All Status') {
            $query->where('status', $filters['status']);
        }

        // 3. Filter by Report Type / Time periods
        $type = $filters['type'] ?? 'bulanan';
        $now = Carbon::now();

        switch ($type) {
            case 'harian':
                $date = $filters['date'] ?? $now->toDateString();
                $query->where('date', $date);
                break;

            case 'mingguan':
                // Filter by selected week range
                $startDate = $filters['start_date'] ?? $now->startOfWeek()->toDateString();
                $endDate = $filters['end_date'] ?? $now->endOfWeek()->toDateString();
                $query->whereBetween('date', [$startDate, $endDate]);
                break;

            case 'bulanan':
                $month = $filters['month'] ?? $now->month;
                $year = $filters['year'] ?? $now->year;
                $query->whereYear('date', $year)->whereMonth('date', $month);
                break;

            case 'tahunan':
                $year = $filters['year'] ?? $now->year;
                $query->whereYear('date', $year);
                break;
        }
    }

    /**
     * Generate date-based monthly recap matrix combined with attendance, holidays, leaves, and effective schedules.
     */
    public function generateMonthlyRecap(array $filters): array
    {
        $recapService = app(\App\Services\MonthlyAttendanceRecapService::class);
        return $recapService->buildCalendarMatrix($filters);
    }
}
