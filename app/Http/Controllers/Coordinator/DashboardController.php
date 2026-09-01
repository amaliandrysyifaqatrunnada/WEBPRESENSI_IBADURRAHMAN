<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Teacher;
use App\Models\Unit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display coordinator dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Anti-IDOR: Scope unit must come from authenticated user's unit_id
        $unitId = $user->unit_id;
        $unit = Unit::find($unitId);

        $teacherCount = Teacher::where('unit_id', $unitId)->where('status', 'active')->count();

        $statsQuery = LeaveRequest::where('unit_id', $unitId);

        $stats = [
            'pending_koordinator' => (clone $statsQuery)->where('status', 'MENUNGGU_PERSETUJUAN_KOORDINATOR')->count(),
            'approved_koordinator' => (clone $statsQuery)->whereIn('status', ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN', 'DISETUJUI'])->count(),
            'rejected_koordinator' => (clone $statsQuery)->where('status', 'DITOLAK_KOORDINATOR')->count(),
            'total_requests' => (clone $statsQuery)->count(),
        ];

        $recentRequests = LeaveRequest::with(['teacher', 'histories'])
            ->where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('coordinator.dashboard', compact('user', 'unit', 'teacherCount', 'stats', 'recentRequests'));
    }
}
