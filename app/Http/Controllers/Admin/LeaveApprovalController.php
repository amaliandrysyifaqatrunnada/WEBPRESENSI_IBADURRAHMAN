<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveApprovalHistory;
use App\Models\Unit;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveApprovalController extends Controller
{
    /**
     * Display list of leave requests for approval.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $units = Unit::all();

        $selectedUnitId = $request->input('unit_id', 'All');
        $selectedType = $request->input('type', 'All');
        $selectedStatus = $request->input('status', 'All');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = LeaveRequest::with(['teacher', 'unit', 'histories']);

        // Scoping for Admin Unit vs Superadmin
        if (!$user->hasRole('superadmin') && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
            $selectedUnitId = $user->unit_id;
        } elseif ($selectedUnitId !== 'All') {
            $query->where('unit_id', $selectedUnitId);
        }

        if ($selectedType !== 'All') {
            $query->where('type', $selectedType);
        }

        if ($selectedStatus !== 'All') {
            $query->where('status', $selectedStatus);
        }

        if ($search) {
            $query->whereHas('teacher', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Calculate statistics for dashboard summary
        $statsQuery = LeaveRequest::query();
        if (!$user->hasRole('superadmin') && $user->unit_id) {
            $statsQuery->where('unit_id', $user->unit_id);
        }
        $stats = [
            'total_izin' => (clone $statsQuery)->where('type', 'izin')->count(),
            'total_sakit' => (clone $statsQuery)->where('type', 'sakit')->count(),
            'total_tanpa_keterangan' => (clone $statsQuery)->where('type', 'tanpa_keterangan')->count(),
            'pending_atasan' => (clone $statsQuery)->where('status', 'MENUNGGU_PERSETUJUAN_ATASAN')->count(),
            'pending_admin' => (clone $statsQuery)->whereIn('status', ['MENUNGGU_PERSETUJUAN_ADMIN', 'DISETUJUI_ATASAN'])->count(),
            'approved' => (clone $statsQuery)->where('status', 'DISETUJUI')->count(),
            'rejected' => (clone $statsQuery)->whereIn('status', ['DITOLAK_ATASAN', 'DITOLAK_ADMIN'])->count(),
        ];

        return view('admin.leaves.index', compact('leaveRequests', 'units', 'stats', 'selectedUnitId', 'selectedType', 'selectedStatus', 'search', 'startDate', 'endDate'));
    }

    /**
     * Approve leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);

        $user = auth()->user();

        // Admin Unit cannot approve requests still waiting for Koordinator approval or rejected by Koordinator
        if (!in_array($leaveRequest->status, ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN']) && !$user->hasRole('superadmin')) {
            abort(403, 'Pengajuan ini harus disetujui oleh Koordinator Paket terlebih dahulu.');
        }

        $note = $request->input('note', 'Disetujui oleh Admin Unit');
        $actorRole = $user->hasRole('superadmin') ? 'superadmin' : 'admin';

        $action = 'approve_admin';
        $newStatus = 'DISETUJUI';

        $leaveRequest->status = $newStatus;
        $leaveRequest->save();

        LeaveApprovalHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'actor_name' => $user->name,
            'actor_role' => $actorRole,
            'action' => $action,
            'note' => $note,
            'created_at' => now(),
        ]);

        activity()
            ->performedOn($leaveRequest)
            ->log("Admin menyetujui pengajuan izin #{$leaveRequest->id} untuk guru {$leaveRequest->teacher->name}");

        return back()->with('success', 'Pengajuan izin berhasil disetujui secara final.');
    }

    /**
     * Reject leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);

        $user = auth()->user();

        if ($leaveRequest->status === 'MENUNGGU_PERSETUJUAN_KOORDINATOR' && !$user->hasRole('superadmin')) {
            abort(403, 'Pengajuan ini harus diproses oleh Koordinator Paket terlebih dahulu.');
        }

        $note = $request->input('note', 'Ditolak oleh Admin Unit');
        $actorRole = $user->hasRole('superadmin') ? 'superadmin' : 'admin';

        $action = 'reject_admin';
        $newStatus = 'DITOLAK_ADMIN';

        $leaveRequest->status = $newStatus;
        $leaveRequest->save();

        LeaveApprovalHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'actor_name' => $user->name,
            'actor_role' => $actorRole,
            'action' => $action,
            'note' => $note,
            'created_at' => now(),
        ]);

        activity()
            ->performedOn($leaveRequest)
            ->log("Admin menolak pengajuan izin #{$leaveRequest->id} untuk guru {$leaveRequest->teacher->name}");

        return back()->with('success', 'Pengajuan izin telah ditolak.');
    }

    /**
     * Download / view private leave attachment safely with authorization and path-traversal protection.
     */
    public function downloadAttachment(LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);

        if (!$leaveRequest->attachment_path) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        $path = $leaveRequest->attachment_path;

        // Path Traversal Check
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(400, 'Invalid file path.');
        }

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan pada disk.');
        }

        $fullPath = Storage::disk('local')->path($path);
        $mimeType = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /**
     * Anti-IDOR Authorization Check for Leave Request Unit Scoping.
     */
    protected function authorizeUnitAccess(LeaveRequest $leaveRequest): void
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin') && $user->unit_id) {
            if ((int)$leaveRequest->unit_id !== (int)$user->unit_id) {
                abort(403, 'Anda tidak memiliki akses untuk memproses pengajuan izin dari unit lain.');
            }
        }
    }
}
