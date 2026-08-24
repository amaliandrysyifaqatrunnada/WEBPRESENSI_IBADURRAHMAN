<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\QrTokenService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected QrTokenService $qrTokenService
    ) {}

    /**
     * Show general attendance settings.
     */
    public function attendance()
    {
        $unitId = auth()->user()->unit_id;

        $settings = [
            'attendance_method' => SchoolSetting::getValue('attendance_method', 'gps', $unitId),
            'late_penalty_nominal' => SchoolSetting::getValue('late_penalty_nominal', '10000', $unitId),
            'qr_rotation_interval' => SchoolSetting::getValue('qr_rotation_interval', '30', $unitId),
            'school_name' => SchoolSetting::getValue('school_name', 'PKBM Ibadurrahman', $unitId),
            'school_address' => SchoolSetting::getValue('school_address', 'Jl. Jatiwaringin Raya No. 12, Pondok Gede, Bekasi', $unitId),
            'work_start_time' => SchoolSetting::getValue('work_start_time', '06:00:00', $unitId),
            'work_end_time' => SchoolSetting::getValue('work_end_time', '15:00:00', $unitId),
            'work_end_time_start' => SchoolSetting::getValue('work_end_time_start', '15:00:00', $unitId),
            'work_end_time_end' => SchoolSetting::getValue('work_end_time_end', '17:00:00', $unitId),
            'late_threshold_time' => SchoolSetting::getValue('late_threshold_time', '06:50:00', $unitId),
            'work_days' => SchoolSetting::getValue('work_days', 'Senin - Jumat', $unitId),
        ];

        // Load schedules for this unit
        $schedules = \App\Models\Schedule::where('unit_id', $unitId)
            ->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        return view('admin.settings.attendance', compact('settings', 'schedules'));
    }

    /**
     * Show GPS Geofencing settings.
     */
    public function gps()
    {
        $unitId = auth()->user()->unit_id;
        $unit = auth()->user()->unit;

        $settings = [
            'latitude' => $unit ? $unit->latitude : SchoolSetting::getValue('school_latitude', '-7.4535', $unitId),
            'longitude' => $unit ? $unit->longitude : SchoolSetting::getValue('school_longitude', '112.7097', $unitId),
            'radius' => $unit ? $unit->gps_radius : SchoolSetting::getValue('school_geofence_radius', '50', $unitId),
            'gps_accuracy_threshold' => SchoolSetting::getValue('gps_accuracy_threshold', '50', $unitId),
        ];

        return view('admin.settings.gps', compact('settings'));
    }

    /**
     * Show rotating QR Code settings.
     */
    public function qr()
    {
        $unitId = auth()->user()->unit_id;
        $interval = (int) SchoolSetting::getValue('qr_rotation_interval', 30, $unitId);
        return view('admin.settings.qr', compact('interval'));
    }

    /**
     * Generate dynamic QR token.
     */
    public function getQrToken(Request $request)
    {
        $unitId = auth()->check() ? auth()->user()->unit_id : $request->input('unit_id');
        $token = $this->qrTokenService->generateToken($unitId);
        
        // Generate dynamic absolute URL with route helper
        $url = route('teacher.attendance', ['qr_token' => $token]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => $url,
        ]);
    }

    /**
     * Save/Update settings key-values.
     */
    public function save(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        $unit = auth()->user()->unit;

        // 1. Sync coordinates to units table if present
        if ($request->has('school_latitude') && $unit) {
            $request->validate([
                'school_latitude' => 'required|numeric|between:-90,90',
                'school_longitude' => 'required|numeric|between:-180,180',
                'school_geofence_radius' => 'required|numeric|min:0.01',
                'gps_accuracy_threshold' => 'nullable|numeric|min:5|max:5000',
            ], [
                'school_latitude.between' => 'Latitude harus bernilai antara -90 sampai 90 derajat.',
                'school_longitude.between' => 'Longitude harus bernilai antara -180 sampai 180 derajat.',
                'school_geofence_radius.min' => 'Radius toleransi GPS harus bernilai positif.',
                'gps_accuracy_threshold.min' => 'Akurasi GPS minimal 5 meter.',
                'gps_accuracy_threshold.max' => 'Akurasi GPS maksimal 5000 meter.',
            ]);

            $unit->update([
                'latitude' => (double) $request->input('school_latitude'),
                'longitude' => (double) $request->input('school_longitude'),
                'gps_radius' => (double) $request->input('school_geofence_radius'),
            ]);

            // Save in SchoolSetting for legacy compatibility
            SchoolSetting::setValue('school_latitude', $request->input('school_latitude'), $unitId);
            SchoolSetting::setValue('school_longitude', $request->input('school_longitude'), $unitId);
            SchoolSetting::setValue('school_geofence_radius', $request->input('school_geofence_radius'), $unitId);
        }

        // 2. Save school profile & config settings
        $data = $request->except(['_token', 'schedules', 'school_latitude', 'school_longitude', 'school_geofence_radius']);
        foreach ($data as $key => $value) {
            if ($value !== null) {
                SchoolSetting::setValue($key, $value, $unitId);
            }
        }

        // 3. Save detailed schedules if submitted
        if ($request->has('schedules')) {
            foreach ($request->input('schedules') as $day => $schedData) {
                \App\Models\Schedule::updateOrCreate(
                    [
                        'unit_id' => $unitId,
                        'day_of_week' => $day,
                    ],
                    [
                        'is_active' => isset($schedData['is_active']),
                        'work_start_time' => $schedData['work_start_time'] ?? '06:00:00',
                        'reward_limit_time' => $schedData['reward_limit_time'] ?? '06:45:00',
                        'late_threshold_time' => $schedData['late_threshold_time'] ?? '06:50:00',
                        'work_end_time' => $schedData['work_end_time'] ?? '15:00:00',
                        'work_end_time_end' => $schedData['work_end_time_end'] ?? '17:00:00',
                    ]
                );
            }
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
