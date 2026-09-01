<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveApprovalHistory;
use App\Models\Teacher;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveApprovalController extends Controller
{
    /**
     * Display list of leave requests for Coordinator's assigned unit.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;
        $unit = Unit::find($unitId);

        $selectedType = $request->input('type', 'All');
        $selectedStatus = $request->input('status', 'All');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = LeaveRequest::with(['teacher', 'unit', 'histories'])
            ->where('unit_id', $unitId);

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

        $statsQuery = LeaveRequest::where('unit_id', $unitId);
        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'MENUNGGU_PERSETUJUAN_KOORDINATOR')->count(),
            'approved_coordinator' => (clone $statsQuery)->whereIn('status', ['DISETUJUI_KOORDINATOR', 'MENUNGGU_PERSETUJUAN_ADMIN', 'DISETUJUI'])->count(),
            'rejected_coordinator' => (clone $statsQuery)->where('status', 'DITOLAK_KOORDINATOR')->count(),
            'total' => (clone $statsQuery)->count(),
        ];

        return view('coordinator.leaves.index', compact(
            'leaveRequests', 'unit', 'stats', 'selectedType', 'selectedStatus', 'search', 'startDate', 'endDate'
        ));
    }

    /**
     * Display detail of a specific leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);
        $leaveRequest->load(['teacher', 'unit', 'histories']);

        return view('coordinator.leaves.show', compact('leaveRequest'));
    }

    /**
     * Approve leave request by Coordinator.
     * Status changes to DISETUJUI_KOORDINATOR (which enters MENUNGGU_PERSETUJUAN_ADMIN pool).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);

        if ($leaveRequest->status !== 'MENUNGGU_PERSETUJUAN_KOORDINATOR') {
            return back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $user = auth()->user();
        $note = $request->input('note', 'Disetujui oleh Koordinator Paket');

        $leaveRequest->status = 'DISETUJUI_KOORDINATOR';
        $leaveRequest->save();

        LeaveApprovalHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'actor_name' => $user->name,
            'actor_role' => 'koordinator',
            'action' => 'approve_coordinator',
            'note' => $note,
            'created_at' => now(),
        ]);

        activity()
            ->performedOn($leaveRequest)
            ->log("Koordinator Paket {$user->unit->name} menyetujui pengajuan izin #{$leaveRequest->id} untuk guru {$leaveRequest->teacher->name}");

        return back()->with('success', 'Pengajuan izin berhasil disetujui dan diteruskan ke Admin Unit.');
    }

    /**
     * Reject leave request by Coordinator.
     * Status changes to DITOLAK_KOORDINATOR. Note is mandatory.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeUnitAccess($leaveRequest);

        if ($leaveRequest->status !== 'MENUNGGU_PERSETUJUAN_KOORDINATOR') {
            return back()->with('error', 'Pengajuan izin ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'note' => 'required|string|min:3',
        ], [
            'note.required' => 'Catatan/alasan penolakan wajib diisi oleh Koordinator.',
            'note.min' => 'Catatan penolakan minimal 3 karakter.',
        ]);

        $user = auth()->user();
        $note = $request->input('note');

        $leaveRequest->status = 'DITOLAK_KOORDINATOR';
        $leaveRequest->save();

        LeaveApprovalHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $user->id,
            'actor_type' => 'user',
            'actor_name' => $user->name,
            'actor_role' => 'koordinator',
            'action' => 'reject_coordinator',
            'note' => $note,
            'created_at' => now(),
        ]);

        activity()
            ->performedOn($leaveRequest)
            ->log("Koordinator Paket {$user->unit->name} menolak pengajuan izin #{$leaveRequest->id} untuk guru {$leaveRequest->teacher->name}");

        return back()->with('success', 'Pengajuan izin telah ditolak oleh Koordinator.');
    }

    /**
     * Download / view private leave attachment securely with unit scoping & path traversal protection.
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
     * Strict Anti-IDOR Scoping Check for Coordinator.
     */
    protected function authorizeUnitAccess(LeaveRequest $leaveRequest): void
    {
        $user = auth()->user();
        if ((int)$leaveRequest->unit_id !== (int)$user->unit_id && !$user->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan izin dari unit/paket lain.');
        }
    }
}
