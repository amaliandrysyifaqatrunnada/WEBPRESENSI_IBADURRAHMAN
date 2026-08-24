<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Teacher;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if a teacher can record their own attendance.
     */
    public function record(Teacher $teacher): bool
    {
        // Active teachers can record their own attendance
        return $teacher->status === 'active';
    }

    /**
     * Determine if a user (admin) can manage attendance records.
     */
    public function manage(User $user): bool
    {
        // Admin user can view and edit all attendances
        return $user->hasRole('admin');
    }
}
