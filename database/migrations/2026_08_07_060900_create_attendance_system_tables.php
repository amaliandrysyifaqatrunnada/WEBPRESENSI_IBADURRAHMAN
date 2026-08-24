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
        // 1. Teachers Table
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password'); // PIN/Password
            $table->string('position');
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Attendances Table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date')->index();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status', 20)->index();
            $table->decimal('penalty', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Attendance Logs Table
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('type', ['clock_in', 'clock_out']);
            $table->double('latitude');
            $table->double('longitude');
            $table->double('distance_meters');
            $table->string('method', 20)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('log_status', 20)->index();
            $table->string('reason', 255)->nullable();
            $table->timestamps();
        });

        // 4. School Settings Table
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('teachers');
    }
};
