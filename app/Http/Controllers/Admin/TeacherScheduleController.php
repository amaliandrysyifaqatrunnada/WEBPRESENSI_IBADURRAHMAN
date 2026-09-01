<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherWorkSchedule;
use App\Models\Unit;
use Illuminate\Http\Request;

class TeacherScheduleController extends Controller
{
    /**
     * Display listing of teachers and their work schedule status.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $units = Unit::all();

        $selectedUnitId = $request->input('unit_id', 'All');
        $search = $request->input('search');

        $query = Teacher::with(['unit', 'workSchedules'])->where('status', 'active');

        // Scoping for Admin Unit vs Superadmin
        if (!$user->hasRole('superadmin') && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
            $selectedUnitId = $user->unit_id;
        } elseif ($selectedUnitId !== 'All') {
            $query->where('unit_id', $selectedUnitId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.teachers.schedule', compact('teachers', 'units', 'selectedUnitId', 'search'));
    }

    /**
     * Show form/details for editing teacher's custom work schedule.
     */
    public function edit(Teacher $teacher)
    {
        $this->authorizeTeacherAccess($teacher);

        $schedules = $teacher->workSchedules->keyBy('day_of_week');

        return view('admin.teachers.schedule_edit', compact('teacher', 'schedules'));
    }

    /**
     * Update teacher custom work schedule settings.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $this->authorizeTeacherAccess($teacher);

        $useCustomSchedule = $request->has('use_custom_schedule') ? (bool)$request->input('use_custom_schedule') : false;
        $teacher->use_custom_schedule = $useCustomSchedule;
        
        if ($request->has('supervisor_id')) {
            $supervisorId = $request->input('supervisor_id');
            if ($supervisorId && (int)$supervisorId !== (int)$teacher->id) {
                $teacher->supervisor_id = $supervisorId;
            } else {
                $teacher->supervisor_id = null;
            }
        }
        
        $teacher->save();

        if ($useCustomSchedule && $request->has('days')) {
            $daysData = $request->input('days', []);
            foreach ($daysData as $dayOfWeek => $data) {
                $dayOfWeek = (int)$dayOfWeek;
                if ($dayOfWeek >= 1 && $dayOfWeek <= 7) {
                    $startTime = $data['start_time'] ?? '07:00';
                    $endTime = $data['end_time'] ?? '15:00';
                    $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

                    TeacherWorkSchedule::updateOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'day_of_week' => $dayOfWeek,
                        ],
                        [
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'is_active' => $isActive,
                        ]
                    );
                }
            }
        }

        activity()
            ->performedOn($teacher)
            ->log("Memperbarui jadwal kerja individual untuk guru {$teacher->name}");

        return redirect()->route('admin.teachers.schedule.index')->with('success', "Jadwal kerja guru {$teacher->name} berhasil diperbarui.");
    }

    /**
     * Anti-IDOR Authorization Check for Teacher Access.
     */
    protected function authorizeTeacherAccess(Teacher $teacher): void
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin') && $user->unit_id) {
            if ((int)$teacher->unit_id !== (int)$user->unit_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengelola jadwal guru di unit lain.');
            }
        }
    }
}
