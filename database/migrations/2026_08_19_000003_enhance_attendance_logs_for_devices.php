<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            // Add device_id
            if (!Schema::hasColumn('attendance_logs', 'device_id')) {
                $table->foreignId('device_id')->nullable()->after('unit_id')->constrained('attendance_devices')->onDelete('set null');
            }

            // Make teacher_id nullable
            try {
                $table->dropForeign('attendance_logs_teacher_id_foreign');
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }

            $table->foreignId('teacher_id')->nullable()->change();

            try {
                $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            } catch (\Exception $e) {
                // Ignore if constraint already exists
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_logs', 'device_id')) {
                try {
                    $table->dropForeign('attendance_logs_device_id_foreign');
                } catch (\Exception $e) {}
                $table->dropColumn('device_id');
            }

            try {
                $table->dropForeign('attendance_logs_teacher_id_foreign');
            } catch (\Exception $e) {}

            $table->foreignId('teacher_id')->nullable(false)->change();

            try {
                $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            } catch (\Exception $e) {}
        });
    }
};
