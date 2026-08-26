<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create units table
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('package_type', ['TK', 'PAKET_A', 'PAKET_B', 'PAKET_C'])->default('PAKET_A');
                $table->string('address')->nullable();
                $table->double('latitude')->nullable();
                $table->double('longitude')->nullable();
                $table->double('gps_radius')->default(50.0); // default 50 meters
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Get coordinates and values from existing school_settings if available
        $existingLat = DB::table('school_settings')->where('key', 'school_latitude')->value('value') ?? -7.4535;
        $existingLng = DB::table('school_settings')->where('key', 'school_longitude')->value('value') ?? 112.7097;
        $existingRad = DB::table('school_settings')->where('key', 'school_geofence_radius')->value('value') ?? 50.0;
        $existingName = DB::table('school_settings')->where('key', 'school_name')->value('value') ?? 'PKBM Ibadurrahman (Sidoarjo) - Paket A';
        $existingAddr = DB::table('school_settings')->where('key', 'school_address')->value('value') ?? 'Jl. Albatros No.154, Kwadengan Barat, Lemahputro, Kec. Sidoarjo, Sidoarjo, Jawa Timur 61213';

        // 2. Insert 4 initial Units if not exists
        $units = [
            [
                'name' => $existingName,
                'package_type' => 'PAKET_A',
                'address' => $existingAddr,
                'latitude' => (double) $existingLat,
                'longitude' => (double) $existingLng,
                'gps_radius' => (double) $existingRad,
                'active' => true,
            ],
            [
                'name' => 'TK PKBM Ibadurrahman',
                'package_type' => 'TK',
                'address' => 'Jl. Albatros No.154 (Area TK), Kwadengan Barat, Lemahputro, Kec. Sidoarjo, Sidoarjo, Jawa Timur 61213',
                'latitude' => (double) $existingLat, // Default to Sidoarjo coordinates initially
                'longitude' => (double) $existingLng,
                'gps_radius' => 50.0,
                'active' => true,
            ],
            [
                'name' => 'PKBM Ibadurrahman - Paket B',
                'package_type' => 'PAKET_B',
                'address' => 'Jl. Albatros No.154 (Area SMP), Kwadengan Barat, Lemahputro, Kec. Sidoarjo, Sidoarjo, Jawa Timur 61213',
                'latitude' => (double) $existingLat,
                'longitude' => (double) $existingLng,
                'gps_radius' => 50.0,
                'active' => true,
            ],
            [
                'name' => 'PKBM Ibadurrahman - Paket C',
                'package_type' => 'PAKET_C',
                'address' => 'Jl. Albatros No.154 (Area SMA), Kwadengan Barat, Lemahputro, Kec. Sidoarjo, Sidoarjo, Jawa Timur 61213',
                'latitude' => (double) $existingLat,
                'longitude' => (double) $existingLng,
                'gps_radius' => 50.0,
                'active' => true,
            ],
        ];

        $unitIds = [];
        foreach ($units as $unit) {
            $id = DB::table('units')->where('name', $unit['name'])->value('id');
            if (!$id) {
                $id = DB::table('units')->insertGetId(array_merge($unit, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $unitIds[$unit['package_type']] = $id;
        }

        $defaultUnitId = $unitIds['PAKET_A'];

        // 3. Add unit_id columns to existing tables
        if (!Schema::hasColumn('users', 'unit_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('teachers', 'unit_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('school_settings', 'unit_id')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('attendances', 'unit_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('attendance_logs', 'unit_id')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            });
        }

        // Drop UNIQUE key from key in school_settings to allow key + unit_id composite
        // We catch exception in case the unique key name is different or doesn't exist
        try {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropUnique('school_settings_key_unique');
            });
        } catch (\Exception $e) {
            // Ignore if already dropped or named differently
        }

        // Add index on key & unit_id composite unique
        try {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->unique(['key', 'unit_id']);
            });
        } catch (\Exception $e) {
            // Ignore if index already exists
        }

        // 4. Map existing data to Paket A (Default Unit)
        DB::table('users')->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);
        DB::table('teachers')->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);
        DB::table('school_settings')->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);
        DB::table('attendances')->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);
        DB::table('attendance_logs')->whereNull('unit_id')->update(['unit_id' => $defaultUnitId]);

        // 5. Seed other 3 Unit Admins
        $adminRole = DB::table('roles')->where('name', 'admin')->where('guard_name', 'web')->first();
        $adminRoleId = $adminRole ? $adminRole->id : null;

        $newAdmins = [
            [
                'name' => 'Admin Unit TK',
                'email' => 'admin_TK@ibadurrahman.sch.id',
                'password' => Hash::make('password'),
                'unit_id' => $unitIds['TK'],
            ],
            [
                'name' => 'Admin Unit Paket B',
                'email' => 'admin_paketB@ibadurrahman.sch.id',
                'password' => Hash::make('password'),
                'unit_id' => $unitIds['PAKET_B'],
            ],
            [
                'name' => 'Admin Unit Paket C',
                'email' => 'admin_paketC@ibadurrahman.sch.id',
                'password' => Hash::make('password'),
                'unit_id' => $unitIds['PAKET_C'],
            ],
        ];

        foreach ($newAdmins as $adminData) {
            $exists = DB::table('users')->where('email', $adminData['email'])->first();
            if (!$exists) {
                $userId = DB::table('users')->insertGetId(array_merge($adminData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Assign role 'admin'
                if ($userId && $adminRoleId) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $adminRoleId,
                        'model_type' => 'App\Models\User',
                        'model_id' => $userId,
                    ]);
                }
            }
        }

        // 6. Create schedules table
        if (!Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->string('day_of_week', 20); // 'monday', 'tuesday', etc.
                $table->boolean('is_active')->default(true);
                $table->time('work_start_time')->default('06:00:00');
                $table->time('reward_limit_time')->default('06:45:00');
                $table->time('late_threshold_time')->default('06:50:00');
                $table->time('work_end_time')->default('15:00:00'); // default Senin-Jumat pulang 15:00
                $table->time('work_end_time_end')->default('17:00:00');
                $table->timestamps();
            });
        }

        // Seed default schedules for all 4 units
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($unitIds as $packageType => $uId) {
            foreach ($days as $day) {
                $isSaturday = $day === 'saturday';
                $isSunday = $day === 'sunday';
                
                $exists = DB::table('schedules')->where('unit_id', $uId)->where('day_of_week', $day)->exists();
                if (!$exists) {
                    DB::table('schedules')->insert([
                        'unit_id' => $uId,
                        'day_of_week' => $day,
                        'is_active' => !$isSunday,
                        'work_start_time' => $isSaturday ? '07:15:00' : '06:00:00',
                        'reward_limit_time' => $isSaturday ? '07:15:00' : '06:45:00',
                        'late_threshold_time' => $isSaturday ? '07:15:00' : '06:50:00',
                        'work_end_time' => $isSaturday ? '13:00:00' : '15:00:00',
                        'work_end_time_end' => $isSaturday ? '15:00:00' : '17:00:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 7. Add status_masuk, status_pulang, reward to attendances table
        if (!Schema::hasColumn('attendances', 'status_masuk')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('status_masuk', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('attendances', 'status_pulang')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('status_pulang', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('attendances', 'reward')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->boolean('reward')->default(false);
            });
        }

        // Map existing attendance status logs to the new columns
        $attendances = DB::table('attendances')->get();
        foreach ($attendances as $att) {
            $statusMasuk = $att->status;
            if ($statusMasuk === 'hadir') {
                $statusMasuk = 'Tepat Waktu';
            } elseif ($statusMasuk === 'terlambat') {
                $statusMasuk = 'Terlambat';
            }

            $reward = false;
            if ($att->clock_in && $att->clock_in <= '06:45:00') {
                $reward = true;
            }

            $statusPulang = null;
            if ($att->clock_out) {
                $statusPulang = 'Normal';
            }

            DB::table('attendances')->where('id', $att->id)->update([
                'status_masuk' => $statusMasuk,
                'status_pulang' => $statusPulang,
                'reward' => $reward,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep empty for absolute safety of existing data
    }
};
