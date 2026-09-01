<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceDevice;
use App\Models\Unit;
use App\Models\SchoolSetting;

class PortalController extends Controller
{
    /**
     * Show the main institutional portal.
     */
    public function index()
    {
        return view('portal');
    }

    /**
     * Show public dynamic QR Code for attendance.
     */
    public function publicQr(Request $request)
    {
        $deviceToken = $request->cookie('school_device_token');

        if (!$deviceToken) {
            return view('face_id.error', [
                'reason' => 'DEVICE_NOT_REGISTERED',
                'message' => 'Perangkat ini belum terdaftar sebagai School Attendance Device.'
            ]);
        }

        $device = AttendanceDevice::where('device_token', $deviceToken)->first();

        if (!$device) {
            return view('face_id.error', [
                'reason' => 'DEVICE_NOT_REGISTERED',
                'message' => 'Perangkat ini tidak terdaftar di database sekolah.'
            ]);
        }

        if (!$device->status) {
            return view('face_id.error', [
                'reason' => 'DEVICE_INACTIVE',
                'message' => 'Perangkat sekolah ini dalam status nonaktif.'
            ]);
        }

        // Allow authorized school devices to show QR codes for all active units
        $units = Unit::where('active', true)->get();
        $schoolName = SchoolSetting::getValue('school_name', 'PKBM Ibadurrahman');
        $interval = (int) SchoolSetting::getValue('qr_rotation_interval', 30);
        return view('qr_public', compact('schoolName', 'interval', 'units'));
    }

    /**
     * Fetch dynamic QR token via public API endpoint.
     */
    public function getPublicQrToken(Request $request)
    {
        $deviceToken = $request->cookie('school_device_token');

        if (!$deviceToken) {
            return response()->json(['success' => false, 'message' => 'Perangkat ini belum terdaftar sebagai perangkat presensi sekolah.'], 403);
        }

        $device = AttendanceDevice::where('device_token', $deviceToken)->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Perangkat ini belum terdaftar sebagai perangkat presensi sekolah.'], 403);
        }

        if (!$device->status) {
            return response()->json(['success' => false, 'message' => 'Perangkat presensi tidak aktif.'], 403);
        }

        $unitId = (int)$request->input('unit_id');

        // Server-side validation: ensure the requested unit exists and is active
        $unit = Unit::where('id', $unitId)->where('active', true)->first();
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit tidak valid atau tidak aktif.'], 403);
        }

        $token = app(\App\Services\QrTokenService::class)->generateToken($unitId);
        $url = route('teacher.attendance', ['qr_token' => $token]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => $url,
        ]);
    }
}
