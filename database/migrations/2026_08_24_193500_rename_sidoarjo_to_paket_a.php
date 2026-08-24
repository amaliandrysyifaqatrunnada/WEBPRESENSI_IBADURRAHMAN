<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Unit Name for PAKET_A (Unit ID 1)
        DB::table('units')
            ->where('package_type', 'PAKET_A')
            ->update(['name' => 'PKBM Ibadurrahman - Paket A']);

        // Update default school name in settings
        DB::table('school_settings')
            ->where('key', 'school_name')
            ->where('value', 'PKBM Ibadurrahman - Cabang Sidoarjo')
            ->update(['value' => 'PKBM Ibadurrahman - Paket A']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('units')
            ->where('name', 'PKBM Ibadurrahman - Paket A')
            ->update(['name' => 'PKBM Ibadurrahman - Cabang Sidoarjo']);

        DB::table('school_settings')
            ->where('key', 'school_name')
            ->where('value', 'PKBM Ibadurrahman - Paket A')
            ->update(['value' => 'PKBM Ibadurrahman - Cabang Sidoarjo']);
    }
};
