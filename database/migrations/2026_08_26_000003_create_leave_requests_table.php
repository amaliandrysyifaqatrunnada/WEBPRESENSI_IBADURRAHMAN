<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->enum('type', ['izin', 'sakit', 'tanpa_keterangan'])->default('izin');
                $table->date('start_date')->index();
                $table->date('end_date')->index();
                $table->text('description');
                $table->string('attachment_path')->nullable();
                $table->enum('status', [
                    'DRAFT',
                    'MENUNGGU_PERSETUJUAN_ATASAN',
                    'DISETUJUI_ATASAN',
                    'DITOLAK_ATASAN',
                    'MENUNGGU_PERSETUJUAN_ADMIN',
                    'DISETUJUI',
                    'DITOLAK_ADMIN'
                ])->default('MENUNGGU_PERSETUJUAN_ATASAN')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
