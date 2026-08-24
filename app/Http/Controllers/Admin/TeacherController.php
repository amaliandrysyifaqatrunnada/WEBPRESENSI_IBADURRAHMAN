<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\TeacherData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService
    ) {}

    /**
     * Display a listing of active/filtered teachers.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'position']);
        $showTrashed = $request->has('trashed');

        // Fetch teachers based on tab filter
        if ($showTrashed) {
            $teachers = $this->teacherService->getTrashedTeachers($filters, 10);
        } else {
            $teachers = $this->teacherService->getTeachers($filters, 10);
        }

        // Get unique positions for the filter dropdown scoped to unit
        $posQuery = \App\Models\Teacher::query();
        if (auth()->guard('web')->check() && auth()->guard('web')->user()->unit_id) {
            $posQuery->where('unit_id', auth()->guard('web')->user()->unit_id);
        }
        $positions = $posQuery->pluck('position')->unique()->filter()->values();

        return view('admin.teachers.index', compact('teachers', 'positions', 'filters', 'showTrashed'));
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $dto = TeacherData::fromRequest($request);
        $this->teacherService->createTeacher($dto);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data tenaga pendidik baru berhasil ditambahkan.');
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(UpdateTeacherRequest $request, int $id)
    {
        $dto = TeacherData::fromRequest($request);
        $this->teacherService->updateTeacher($id, $dto);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data tenaga pendidik berhasil diperbarui.');
    }

    /**
     * Soft delete the specified teacher from storage.
     */
    public function destroy(int $id)
    {
        $this->teacherService->deleteTeacher($id);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data tenaga pendidik berhasil dihapus sementara (soft-deleted).');
    }

    /**
     * Restore the specified soft-deleted teacher.
     */
    public function restore(int $id)
    {
        $this->teacherService->restoreTeacher($id);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Data tenaga pendidik berhasil dipulihkan.');
    }

    /**
     * Export teachers data as Excel, CSV, or PDF.
     */
    public function export(Request $request, string $format)
    {
        $filters = $request->only(['search', 'status', 'position']);
        $teachers = $this->teacherService->getAllTeachers($filters);

        $filename = 'Data_Guru_' . date('Ymd_His');

        if ($format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\TeacherExport($teachers), 
                $filename . '.xlsx', 
                \Maatwebsite\Excel\Excel::XLSX
            );
        } elseif ($format === 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\TeacherExport($teachers), 
                $filename . '.csv', 
                \Maatwebsite\Excel\Excel::CSV
            );
        } elseif ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.teachers.pdf', compact('teachers'));
            return $pdf->download($filename . '.pdf');
        }

        return redirect()->back()->with('error', 'Format ekspor tidak didukung.');
    }

    /**
     * Import teachers from Excel or CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'Harap pilih file terlebih dahulu.',
            'file.mimes' => 'Format file tidak didukung. Harap gunakan file .xlsx atau .csv'
        ]);

        try {
            $result = $this->teacherService->importTeachers($request->file('file'));

            if ($result['success']) {
                return redirect()->route('admin.teachers.index')
                    ->with('success', "Impor berhasil! {$result['success_count']} data guru telah berhasil dimasukkan.");
            } else {
                return redirect()->route('admin.teachers.index')
                    ->withErrors($result['errors'])
                    ->with('error_import', 'Impor gagal karena terdapat kesalahan data (tidak ada data yang dimasukkan).');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal memproses berkas: ' . $e->getMessage());
        }
    }

    /**
     * Show the face ID registration screen.
     */
    public function showFaceIdRegistration(int $id)
    {
        $teacher = $this->teacherService->findTeacher($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola data pendidik ini.');
        }

        return view('admin.teachers.face_id', compact('teacher'));
    }

    /**
     * Store the face template / embedding for a teacher.
     */
    public function registerFaceId(Request $request, int $id)
    {
        $teacher = $this->teacherService->findTeacher($id);
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengelola data pendidik ini.'
            ], 403);
        }

        $request->validate([
            'face_template' => 'required|array|size:128',
            'face_template.*' => 'required|numeric',
        ]);

        $teacher->update([
            'face_registered' => true,
            'face_registered_at' => now(),
            'face_template' => $request->input('face_template'),
        ]);

        activity()
            ->performedOn($teacher)
            ->log("Admin mendaftarkan template wajah Face ID untuk guru {$teacher->name}");

        return response()->json([
            'success' => true,
            'message' => 'Wajah tenaga pendidik berhasil didaftarkan.'
        ]);
    }

    /**
     * Delete/Reset face ID registration for a teacher.
     */
    public function deleteFaceId(int $id)
    {
        $teacher = $this->teacherService->findTeacher($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola data pendidik ini.');
        }

        $teacher->update([
            'face_registered' => false,
            'face_registered_at' => null,
            'face_template' => null,
        ]);

        activity()
            ->performedOn($teacher)
            ->log("Admin menghapus template wajah Face ID untuk guru {$teacher->name}");

        return redirect()->route('admin.teachers.index')
            ->with('success', "Data Face ID untuk guru {$teacher->name} berhasil di-reset.");
    }
}
