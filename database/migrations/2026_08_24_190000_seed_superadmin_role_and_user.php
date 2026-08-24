<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Roles
        $superadminRole = Role::findOrCreate('superadmin', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');

        // 2. Create Default Superadmin User
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@ibadurrahman.sch.id'],
            [
                'name' => 'Superadmin PKBM',
                'password' => Hash::make('password'),
                'unit_id' => null, // Superadmin sees all units
            ]
        );
        $superadmin->assignRole($superadminRole);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $superadmin = User::where('email', 'superadmin@ibadurrahman.sch.id')->first();
        if ($superadmin) {
            $superadmin->removeRole('superadmin');
            $superadmin->forceDelete();
        }

        $role = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
        if ($role) {
            $role->delete();
        }
    }
};
