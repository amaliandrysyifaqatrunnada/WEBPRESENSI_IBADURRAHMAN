<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_requests')) {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status VARCHAR(100) NOT NULL DEFAULT 'MENUNGGU_PERSETUJUAN_KOORDINATOR'");
        }
    }

    public function down(): void
    {
        // No-op
    }
};
