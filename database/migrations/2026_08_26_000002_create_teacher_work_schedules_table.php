<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('teachers', 'use_custom_schedule')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('use_custom_schedule')->default(false);
            });
        }

        if (!Schema::hasColumn('teachers', 'supervisor_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('supervisor_id')->nullable()->constrained('teachers')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('teacher_work_schedules')) {
            Schema::create('teacher_work_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->unsignedTinyInteger('day_of_week')->comment('1=Senin, 2=Selasa, ..., 7=Minggu');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['teacher_id', 'day_of_week']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_work_schedules');
        if (Schema::hasColumn('teachers', 'use_custom_schedule')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('use_custom_schedule');
            });
        }
        if (Schema::hasColumn('teachers', 'supervisor_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropForeign(['supervisor_id']);
                $table->dropColumn('supervisor_id');
            });
        }
    }
};
