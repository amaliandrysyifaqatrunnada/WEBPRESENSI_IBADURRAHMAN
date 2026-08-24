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
        // 1. Add accuracy column to attendance_logs if it doesn't exist
        if (!Schema::hasColumn('attendance_logs', 'accuracy')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->double('accuracy')->nullable()->after('longitude');
            });
        }

        // 2. Create qr_token_usages table if it doesn't exist
        if (!Schema::hasTable('qr_token_usages')) {
            Schema::create('qr_token_usages', function (Blueprint $table) {
                $table->id();
                $table->string('token_hash', 64)->unique();
                $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->string('attendance_type', 20); // 'clock_in', 'clock_out'
                $table->double('latitude');
                $table->double('longitude');
                $table->double('accuracy')->nullable();
                $table->timestamp('used_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_token_usages');

        if (Schema::hasColumn('attendance_logs', 'accuracy')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('accuracy');
            });
        }
    }
};
