<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display the presence reports dashboard with statistics, trends, and listing tables.
     */
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);

        // Fetch paginated listing
        $attendances = $this->reportService->getFilteredAttendancesPaginated($filters, 15);

        // Fetch statistics summary
        $stats = $this->reportService->getReportStats($filters);

        // Fetch Chart.js trend dataset
        $chartData = $this->reportService->getChartData($filters);

        // Fetch list of teachers for dropdown filter scoped to unit
        $teacherQuery = Teacher::where('status', 'active');
        if (auth()->user()->unit_id) {
            $teacherQuery->where('unit_id', auth()->user()->unit_id);
        }
        $teachers = $teacherQuery->orderBy('name')->get();

        return view('admin.reports.index', compact('attendances', 'stats', 'chartData', 'teachers', 'filters'));
    }

    /**
     * Export report data as Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->parseFilters($request);
        $attendances = $this->reportService->getFilteredAttendances($filters);

        return Excel::download(new AttendanceExport($attendances), 'Laporan_Kehadiran_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Export report data as PDF.
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->parseFilters($request);
        $attendances = $this->reportService->getFilteredAttendances($filters);
        $stats = $this->reportService->getReportStats($filters);
        $unit = auth()->user()->unit;

        $pdf = Pdf::loadView('admin.reports.pdf', compact('attendances', 'stats', 'filters', 'unit'));
        
        return $pdf->download('Laporan_Kehadiran_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Helper to normalize filter options.
     */
    protected function parseFilters(Request $request): array
    {
        $now = Carbon::now();

        return [
            'type' => $request->input('type', 'bulanan'),
            'date' => $request->input('date', $now->toDateString()),
            'start_date' => $request->input('start_date', $now->startOfWeek()->toDateString()),
            'end_date' => $request->input('end_date', $now->endOfWeek()->toDateString()),
            'month' => $request->input('month', $now->month),
            'year' => $request->input('year', $now->year),
            'teacher_id' => $request->input('teacher_id', 'All Teachers'),
            'status' => $request->input('status', 'All Status'),
        ];
    }
}
