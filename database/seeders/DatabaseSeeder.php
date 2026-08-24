<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Roles
        $adminRole = Role::findOrCreate('admin', 'web');
        $teacherRole = Role::findOrCreate('teacher', 'teacher');

        // 2. Create Default Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@ibadurrahman.sch.id'],
            [
                'name' => 'Administrator PKBM',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // 3. Create Default Teacher User (Matching Stitch mockup profile "Budi Santoso, S.Pd")
        $teacher = Teacher::updateOrCreate(
            ['email' => 'teacher@ibadurrahman.sch.id'],
            [
                'nip' => '198506152010011012',
                'name' => 'Budi Santoso, S.Pd',
                'password' => Hash::make('password'), // PIN/Password
                'position' => 'Guru Matematika',
                'phone' => '0812-3456-7890',
                'avatar' => null,
                'status' => 'active',
            ]
        );
        $teacher->assignRole($teacherRole);

        // 4. Create Default School Settings
        $settings = [
            'attendance_method' => 'gps', // default method
            'school_latitude' => '-7.4535',
            'school_longitude' => '112.7097',
            'school_geofence_radius' => '50', // standard GPS accuracy radius (50 meters)
            'late_penalty_nominal' => '10000', // Rp 10.000 / day late
            'qr_rotation_interval' => '30', // refresh QR code every 30s
            'school_name' => 'PKBM Ibadurrahman - Paket A',
            'school_address' => 'Jl. Albatros No.154, Kwadengan Barat, Lemahputro, Kec. Sidoarjo, Kabupaten Sidoarjo, Jawa Timur 61213',
            'work_start_time' => '06:00:00',
            'late_threshold_time' => '06:50:00',
            'work_end_time' => '13:00:00',
            'work_end_time_start' => '15:00:00',
            'work_end_time_end' => '17:00:00',
            'work_days' => 'Senin - Jumat',
        ];

        foreach ($settings as $key => $value) {
            SchoolSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
