<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'penalty',
        'unit_id',
        'status_masuk',
        'status_pulang',
        'reward',
    ];

    /**
     * Get the unit associated with this attendance record.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the teacher associated with this attendance record.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the logs for this attendance record.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Get the method used for check-in.
     */
    public function getCheckInMethodAttribute(): string
    {
        $log = $this->logs->where('type', 'clock_in')->where('log_status', 'accepted')->first();
        if ($log) {
            switch ($log->method) {
                case 'qr':
                    return 'QR Code';
                case 'gps':
                    return 'GPS Geofence';
                case 'face_id':
                    return 'Face ID';
                case 'manual':
                    return 'Manual';
                default:
                    return strtoupper($log->method);
            }
        }
        return '-';
    }
}
