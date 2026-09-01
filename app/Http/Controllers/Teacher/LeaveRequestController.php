<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Display list of teacher's own leave requests.
     */
    public function index()
    {
        $teacher = auth('teacher')->user();
        $leaveRequests = LeaveRequest::with(['histories'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('teacher.leaves.index', compact('leaveRequests', 'teacher'));
    }

    /**
     * Store new leave request submitted by teacher.
     */
    public function store(Request $request)
    {
        $teacher = auth('teacher')->user();

        $validated = $request->validate([
            'type' => 'required|in:izin,sakit',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string|min:5',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
        ], [
            'type.required' => 'Jenis ketidakhadiran wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'description.required' => 'Keterangan/alasan wajib diisi.',
            'description.min' => 'Keterangan minimal harus 5 karakter.',
            'attachment.mimes' => 'Lampiran harus berupa file PDF, JPG, JPEG, atau PNG.',
            'attachment.max' => 'Ukuran maksimal lampiran adalah 5MB.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            // Save in private directory: storage/app/private/leave_attachments/
            $file = $request->file('attachment');
            $filename = 'leave_' . $teacher->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('private/leave_attachments', $filename, 'local');
        }

        $leaveRequest = LeaveRequest::create([
            'teacher_id' => $teacher->id,
            'unit_id' => $teacher->unit_id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'],
            'attachment_path' => $attachmentPath,
            'status' => 'MENUNGGU_PERSETUJUAN_KOORDINATOR',
            'submitted_at' => now(),
        ]);

        // Record initial approval history
        LeaveApprovalHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'actor_id' => $teacher->id,
            'actor_type' => 'teacher',
            'actor_name' => $teacher->name,
            'actor_role' => 'teacher',
            'action' => 'submit',
            'note' => 'Mengajukan ' . strtoupper($validated['type']) . ': ' . $validated['description'],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan izin berhasil dikirim dan sedang menunggu persetujuan Koordinator Paket.');
    }

    /**
     * Download/view teacher's own private leave attachment securely.
     */
    public function downloadAttachment(LeaveRequest $leaveRequest)
    {
        $teacher = auth('teacher')->user();

        // Ownership Check (Anti-IDOR)
        if ((int)$leaveRequest->teacher_id !== (int)$teacher->id) {
            abort(403, 'Anda tidak memiliki akses ke berkas lampiran ini.');
        }

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
}
