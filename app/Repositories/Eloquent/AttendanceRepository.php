<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Support\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * Find attendance by teacher and date.
     */
    public function findByTeacherAndDate(int $teacherId, string $date): ?Attendance
    {
        return Attendance::where('teacher_id', $teacherId)
            ->where('date', $date)
            ->first();
    }

    /**
     * Create or update attendance.
     */
    public function save(array $data): Attendance
    {
        $updateFields = [
            'status' => $data['status'],
            'penalty' => $data['penalty'] ?? 0.00,
        ];

        if (array_key_exists('clock_in', $data)) {
            $updateFields['clock_in'] = $data['clock_in'];
        }
        if (array_key_exists('clock_out', $data)) {
            $updateFields['clock_out'] = $data['clock_out'];
        }
        if (array_key_exists('unit_id', $data)) {
            $updateFields['unit_id'] = $data['unit_id'];
        }
        if (array_key_exists('status_masuk', $data)) {
            $updateFields['status_masuk'] = $data['status_masuk'];
        }
        if (array_key_exists('status_pulang', $data)) {
            $updateFields['status_pulang'] = $data['status_pulang'];
        }
        if (array_key_exists('reward', $data)) {
            $updateFields['reward'] = $data['reward'];
        }

        return Attendance::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'date' => $data['date'],
            ],
            $updateFields
        );
    }

    /**
     * Create an attendance audit log.
     */
    public function createLog(array $data): AttendanceLog
    {
        return AttendanceLog::create($data);
    }

    /**
     * Get attendance history for a teacher.
     */
    public function getTeacherHistory(int $teacherId, int $limit = 10): Collection
    {
        return Attendance::where('teacher_id', $teacherId)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }
}
