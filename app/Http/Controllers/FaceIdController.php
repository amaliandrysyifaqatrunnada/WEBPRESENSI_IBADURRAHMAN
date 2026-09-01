<?php

namespace App\Http\Controllers;

use App\DTOs\AttendanceSubmitData;
use App\Models\AttendanceDevice;
use App\Models\AttendanceLog;
use App\Models\Teacher;
use App\Models\Unit;
use App\Services\AttendanceService;
use App\Services\GeofencingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceIdController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected GeofencingService $geofencingService
    ) {}

    public function index(Request $request)
    {
        $deviceToken = $request->cookie('school_device_token');
        $device = null;
        $unit = null;

        if ($deviceToken) {
            $device = AttendanceDevice::where('device_token', $deviceToken)->where('status', true)->first();
            if ($device) {
                $unit = $device->unit;
            }
        }

        $units = Unit::where('active', true)->orderBy('name', 'asc')->get();

        return view('face_id.index', compact('device', 'unit', 'units'));
    }

    /**
     * Search teacher by Name or NIP, scoped to device unit (Anti-IDOR).
     */
    public function searchTeacher(Request $request)
    {
        $queryStr = $request->input('query');
        $unitId = $request->input('unit_id');
        if (empty($queryStr)) {
            return response()->json(['success' => false, 'message' => 'Masukkan nama atau nomor ID pendidik.'], 422);
        }

        $query = Teacher::where('status', 'active');

        if (!empty($unitId)) {
            $query->where('unit_id', $unitId);
        } else {
            $deviceToken = $request->cookie('school_device_token');
            if ($deviceToken) {
                $device = AttendanceDevice::where('device_token', $deviceToken)->where('status', true)->first();
                if ($device) {
                    $query->where('unit_id', $device->unit_id);
                }
            } else {
                if (auth()->guard('teacher')->check()) {
                    $query->where('unit_id', auth()->guard('teacher')->user()->unit_id);
                }
            }
        }

        $teacher = $query->where(function($q) use ($queryStr) {
            $q->where('name', 'like', "%{$queryStr}%")
              ->orWhere('nip', $queryStr);
        })->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Tenaga pendidik tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'unit_name' => $teacher->unit->name ?? '-',
                'unit_latitude' => (float)($teacher->unit->latitude ?? 0),
                'unit_longitude' => (float)($teacher->unit->longitude ?? 0)
            ]
        ]);
    }

    /**
     * Helper: Euclidean Distance calculation between two arrays of floats.
     */
    protected function calculateEuclideanDistance(array $arr1, array $arr2): float
    {
        if (count($arr1) !== count($arr2)) {
            return 999.0;
        }

        $sum = 0.0;
        for ($i = 0; $i < count($arr1); $i++) {
            $diff = (float)$arr1[$i] - (float)$arr2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Verify Face ID and record attendance.
     */
    public function submitAttendance(Request $request)
    {
        $request->validate([
            'teacher_id' => 'nullable|integer',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
            'action_type' => 'required|string|in:check_in,check_out',
            'selfie' => 'required|string',
            'face_descriptor' => 'nullable|array',
        ]);

        $selfiePath = null;
        if ($request->filled('selfie')) {
            $selfieData = $request->input('selfie');
            if (str_contains($selfieData, 'base64,')) {
                $parts = explode('base64,', $selfieData);
                $image = $parts[1] ?? $parts[0];
            } else {
                $image = $selfieData;
            }
            $image = str_replace(' ', '+', $image);
            $imageDecoded = base64_decode($image);
            if ($imageDecoded) {
                $imageName = 'selfie_' . \Illuminate\Support\Str::uuid()->toString() . '.jpg';
                $relativePath = 'selfies/' . $imageName;
                Storage::disk('local')->put($relativePath, $imageDecoded);
                $selfiePath = $relativePath;
            }
        }

        // 2. Resolve Teacher (Anti-IDOR: Prioritize authenticated session, then request parameter)
        $teacher = null;
        if (auth()->guard('teacher')->check()) {
            $teacher = auth()->guard('teacher')->user();
        } else {
            $teacherId = $request->input('teacher_id');
            $teacher = Teacher::where('id', $teacherId)->where('status', 'active')->first();
        }

        if (!$teacher) {
            // Log attempt with null teacher_id
            AttendanceLog::create([
                'teacher_id' => null,
                'type' => $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out',
                'latitude' => (double)$request->input('latitude'),
                'longitude' => (double)$request->input('longitude'),
                'accuracy' => (double)$request->input('accuracy'),
                'distance_meters' => 0,
                'method' => 'face_id',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'log_status' => 'rejected',
                'reason' => 'TEACHER_NOT_FOUND',
                'unit_id' => null,
                'device_id' => null,
                'selfie_path' => $selfiePath,
            ]);
            return response()->json(['success' => false, 'message' => 'TEACHER_NOT_FOUND'], 404);
        }

        $unitId = $teacher->unit_id;
        $unit = $teacher->unit;

        // Resolve Device ID if school device cookie is present
        $deviceToken = $request->cookie('school_device_token');
        $device = null;
        if ($deviceToken) {
            $device = AttendanceDevice::where('device_token', $deviceToken)->first();
        }
        $deviceId = $device ? $device->id : null;

        // 3. Face Enrollment & Matching (Biometrics Verification - Bypassed completely as per user request to act as pure selfie)
        // No biometrics face template comparison is performed.

        // 4. GPS Accuracy Validation (Against teacher's unit settings)
        $gpsAccuracyThreshold = (float) \App\Models\SchoolSetting::getValue('gps_accuracy_threshold', 50.0, $unitId);
        if ($request->input('accuracy') > $gpsAccuracyThreshold) {
            AttendanceLog::create([
                'teacher_id' => $teacher->id,
                'type' => $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out',
                'latitude' => (double)$request->input('latitude'),
                'longitude' => (double)$request->input('longitude'),
                'accuracy' => (double)$request->input('accuracy'),
                'distance_meters' => 0,
                'method' => 'face_id',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'log_status' => 'rejected',
                'reason' => 'GPS_ACCURACY_TOO_LOW',
                'unit_id' => $unitId,
                'device_id' => $deviceId,
                'selfie_path' => $selfiePath,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'GPS_ACCURACY_TOO_LOW'
            ], 422);
        }

        // 5. Geofence Distance Validation (Haversine)
        if ($unit->latitude === null || $unit->longitude === null) {
            AttendanceLog::create([
                'teacher_id' => $teacher->id,
                'type' => $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out',
                'latitude' => (double)$request->input('latitude'),
                'longitude' => (double)$request->input('longitude'),
                'accuracy' => (double)$request->input('accuracy'),
                'distance_meters' => 0,
                'method' => 'face_id',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'log_status' => 'rejected',
                'reason' => 'UNIT_GPS_NOT_CONFIGURED',
                'unit_id' => $unitId,
                'device_id' => $deviceId,
                'selfie_path' => $selfiePath,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lokasi GPS unit Anda belum dikonfigurasi oleh administrator.'
            ], 422);
        }
        $deviceDist = $this->geofencingService->calculateDistance(
            (float)$request->input('latitude'),
            (float)$request->input('longitude'),
            (float)$unit->latitude,
            (float)$unit->longitude
        );

        if ($deviceDist > $unit->gps_radius) {
            AttendanceLog::create([
                'teacher_id' => $teacher->id,
                'type' => $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out',
                'latitude' => (double)$request->input('latitude'),
                'longitude' => (double)$request->input('longitude'),
                'accuracy' => (double)$request->input('accuracy'),
                'distance_meters' => $deviceDist,
                'method' => 'face_id',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'log_status' => 'rejected',
                'reason' => 'OUTSIDE_GEOFENCE',
                'unit_id' => $unitId,
                'device_id' => $deviceId,
                'selfie_path' => $selfiePath,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'OUTSIDE_GEOFENCE'
            ], 422);
        }

        // 6. Core Attendance Logging using AttendanceService existing
        $dto = new AttendanceSubmitData(
            teacher_id: $teacher->id,
            action_type: $request->input('action_type'),
            latitude: (float)$request->input('latitude'),
            longitude: (float)$request->input('longitude'),
            accuracy: (float)$request->input('accuracy'),
            method: 'face_id',
            qr_token: null,
            status: null,
            date: null,
            ip_address: $request->ip(),
            user_agent: $request->header('User-Agent')
        );

        try {
            $result = $this->attendanceService->submitAttendance($dto);

            // Update newly created log with device_id and selfie_path
            $log = AttendanceLog::where('teacher_id', $teacher->id)
                ->where('method', 'face_id')
                ->where('type', $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out')
                ->latest()
                ->first();

            if ($log) {
                $log->update([
                    'device_id' => $deviceId,
                    'selfie_path' => $selfiePath
                ]);
            }

            if ($device) {
                $device->update(['last_used_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'time' => $result['time'] ?? now()->format('H:i'),
                'teacher_name' => $teacher->name,
                'unit_name' => $unit->name,
                'type' => $request->input('action_type'),
                'status' => $result['status'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Check specific common failure reasons and log them
            $reason = 'REJECTED';
            $errMsg = $e->getMessage();
            if (str_contains($errMsg, 'sudah melakukan presensi masuk')) {
                $reason = 'ALREADY_CHECKED_IN';
            } elseif (str_contains($errMsg, 'sudah melakukan presensi pulang')) {
                $reason = 'ALREADY_CHECKED_OUT';
            } elseif (str_contains($errMsg, 'belum melakukan presensi masuk')) {
                $reason = 'NO_CHECK_IN_RECORD';
            }

            AttendanceLog::create([
                'teacher_id' => $teacher->id,
                'type' => $request->input('action_type') === 'check_in' ? 'clock_in' : 'clock_out',
                'latitude' => (double)$request->input('latitude'),
                'longitude' => (double)$request->input('longitude'),
                'accuracy' => (double)$request->input('accuracy'),
                'distance_meters' => $deviceDist,
                'method' => 'face_id',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'log_status' => 'rejected',
                'reason' => $reason,
                'unit_id' => $unitId,
                'device_id' => $deviceId,
                'selfie_path' => $selfiePath,
            ]);

            return response()->json([
                'success' => false,
                'message' => $errMsg
            ], 422);
        }
    }

    /**
     * Securely serve a selfie image to authorized admins.
     */
    public function showSelfie(Request $request, $filename)
    {
        // 1. Authenticate Admin (auth:web guard is already handled by middleware)
        $admin = auth()->user();
        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        // 2. Prevent path traversal attacks
        $filename = basename($filename);

        // 3. Locate log associated with this selfie to check unit scoping
        $log = AttendanceLog::where('selfie_path', 'like', "%{$filename}")->first();
        if (!$log) {
            abort(404, 'Selfie record not found');
        }

        // 4. Check Unit scoping (Anti-IDOR)
        if ($admin->unit_id && $admin->unit_id !== $log->unit_id) {
            abort(403, 'Akses ditolak. Berkas ini milik unit lain.');
        }

        // 5. Serve the file from secure storage
        $relativePath = 'selfies/' . $filename;
        if (Storage::disk('local')->exists($relativePath)) {
            $contents = Storage::disk('local')->get($relativePath);
        } else {
            // Fallback for older files stored in storage/app/selfies/
            $oldPath = storage_path('app/selfies/' . $filename);
            if (file_exists($oldPath)) {
                $contents = file_get_contents($oldPath);
            } else {
                abort(404, 'File gambar tidak ditemukan di server.');
            }
        }

        return response($contents)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Get active teachers by unit ID.
     */
    public function getTeachersByUnit(Request $request)
    {
        $unitId = $request->input('unit_id');
        if (empty($unitId)) {
            return response()->json([]);
        }

        $teachers = Teacher::where('unit_id', $unitId)
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nip']);

        return response()->json($teachers);
    }
}
