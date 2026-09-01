<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_approval_histories')) {
            Schema::create('leave_approval_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_request_id')->constrained('leave_requests')->onDelete('cascade');
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('actor_type', 50)->nullable()->comment('user or teacher');
                $table->string('actor_name')->nullable();
                $table->string('actor_role', 50)->comment('teacher, atasan, admin, superadmin');
                $table->string('action', 50)->comment('submit, approve_atasan, reject_atasan, approve_admin, reject_admin');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approval_histories');
    }
};
