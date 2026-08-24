<?php

namespace App\Http\Controllers\Teacher;

use App\DTOs\AttendanceSubmitData;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Services\AttendanceService;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Show the teacher attendance dashboard along with historical logs.
     */
    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $today = Carbon::today()->toDateString();

        // 1. Fetch today's attendance summary
        $attendance = $this->attendanceRepository->findByTeacherAndDate($teacher->id, $today);

        // 2. Fetch school settings scoped to teacher's unit
        $unit = $teacher->unit;
        $schoolSettings = [
            'latitude' => $unit ? $unit->latitude : SchoolSetting::getValue('school_latitude', '-7.4535', $teacher->unit_id),
            'longitude' => $unit ? $unit->longitude : SchoolSetting::getValue('school_longitude', '112.7097', $teacher->unit_id),
            'radius' => $unit ? $unit->gps_radius : SchoolSetting::getValue('school_geofence_radius', '50', $teacher->unit_id),
            'method' => SchoolSetting::getValue('attendance_method', 'gps', $teacher->unit_id),
        ];

        // 3. Fetch past attendance history
        $history = $this->attendanceRepository->getTeacherHistory($teacher->id, 10);

        return view('teacher.attendance', compact('teacher', 'attendance', 'schoolSettings', 'history'));
    }

    /**
     * Handle AJAX/Fetch attendance clock-in and clock-out submissions.
     */
    public function submit(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        // Check policy record permission
        if ($teacher->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Status tenaga pendidik Anda dinonaktifkan. Silakan hubungi admin.',
            ], 403);
        }

        // Validate request data
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'action_type' => 'required|string|in:check_in,check_out',
            'method' => 'required|string|in:gps,qr',
            'qr_token' => 'required_if:method,qr|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Build DTO - teacher_id is bound server-side from session for absolute security (anti-IDOR)
        $dto = AttendanceSubmitData::fromRequest($request, $teacher->id);

        try {
            // Process presence with validation and database transaction
            $result = $this->attendanceService->submitAttendance($dto);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'time' => $result['time'] ?? null,
                'type' => $dto->action_type,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
