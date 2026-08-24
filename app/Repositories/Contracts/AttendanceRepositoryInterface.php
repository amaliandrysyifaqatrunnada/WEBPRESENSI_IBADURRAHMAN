<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Support\Collection;

interface AttendanceRepositoryInterface
{
    /**
     * Find attendance by teacher and date.
     */
    public function findByTeacherAndDate(int $teacherId, string $date): ?Attendance;

    /**
     * Create or update attendance.
     */
    public function save(array $data): Attendance;

    /**
     * Create an attendance audit log.
     */
    public function createLog(array $data): AttendanceLog;

    /**
     * Get attendance history for a teacher.
     */
    public function getTeacherHistory(int $teacherId, int $limit = 10): Collection;
}
