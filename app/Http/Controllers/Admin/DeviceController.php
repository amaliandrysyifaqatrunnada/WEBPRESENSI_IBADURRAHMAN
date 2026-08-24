<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Helper to enforce unit scoping (Anti-IDOR).
     */
    protected function checkUnitPermission(AttendanceDevice $device)
    {
        $adminUnitId = auth()->user()->unit_id;
        if ($adminUnitId && $device->unit_id !== $adminUnitId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola perangkat unit lain.');
        }
    }

    /**
     * Display a listing of unit devices.
     */
    public function index()
    {
        $unitId = auth()->user()->unit_id;
        
        $devices = AttendanceDevice::where('unit_id', $unitId)->get();
        $unit = Unit::find($unitId);

        // Check if the current browser is activated
        $currentDeviceToken = request()->cookie('school_device_token');
        $activeDeviceOnBrowser = null;
        if ($currentDeviceToken) {
            $activeDeviceOnBrowser = AttendanceDevice::where('device_token', $currentDeviceToken)->first();
        }

        return view('admin.devices.index', compact('devices', 'unit', 'activeDeviceOnBrowser'));
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:100',
        ]);

        $unitId = auth()->user()->unit_id;

        AttendanceDevice::create([
            'unit_id' => $unitId,
            'device_name' => $request->input('device_name'),
            'device_token' => Str::uuid()->toString() . '-' . Str::random(16),
            'status' => true,
        ]);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Perangkat sekolah baru berhasil didaftarkan.');
    }

    /**
     * Toggle device status (active/inactive).
     */
    public function toggle(int $id)
    {
        $device = AttendanceDevice::findOrFail($id);
        $this->checkUnitPermission($device);

        $device->status = !$device->status;
        $device->save();

        $statusStr = $device->status ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.devices.index')
            ->with('success', "Perangkat {$device->device_name} berhasil {$statusStr}.");
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(int $id)
    {
        $device = AttendanceDevice::findOrFail($id);
        $this->checkUnitPermission($device);

        // If the browser currently has this device bound, clear it
        $currentDeviceToken = request()->cookie('school_device_token');
        $cookie = null;
        if ($currentDeviceToken && $currentDeviceToken === $device->device_token) {
            $cookie = cookie()->forget('school_device_token');
        }

        $device->delete();

        $response = redirect()->route('admin.devices.index')
            ->with('success', 'Perangkat sekolah berhasil dihapus.');

        if ($cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }

    /**
     * Bind/activate the current browser with the device.
     */
    public function activateBrowser(int $id)
    {
        $device = AttendanceDevice::findOrFail($id);
        $this->checkUnitPermission($device);

        if (!$device->status) {
            return redirect()->route('admin.devices.index')
                ->with('error', 'Perangkat yang dinonaktifkan tidak dapat diaktifkan pada browser.');
        }

        // Set secure long-lived cookie (5 years)
        $cookie = cookie()->forever('school_device_token', $device->device_token);

        return redirect()->route('admin.devices.index')
            ->with('success', "Browser ini berhasil diikat ke perangkat: {$device->device_name}")
            ->withCookie($cookie);
    }

    /**
     * Unbind/deactivate the current browser from the device.
     */
    public function deactivateBrowser()
    {
        $cookie = cookie()->forget('school_device_token');

        return redirect()->route('admin.devices.index')
            ->with('success', 'Ikatan Perangkat Sekolah pada browser ini berhasil dilepas.')
            ->withCookie($cookie);
    }
}
