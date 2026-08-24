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
        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'face_registered')) {
                $table->boolean('face_registered')->default(false)->after('status');
            }
            if (!Schema::hasColumn('teachers', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_registered');
            }
            if (!Schema::hasColumn('teachers', 'face_template')) {
                $table->longText('face_template')->nullable()->after('face_registered_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['face_registered', 'face_registered_at', 'face_template']);
        });
    }
};
