<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDevice extends Model
{
    use HasFactory;

    protected $table = 'attendance_devices';

    protected $fillable = [
        'unit_id',
        'device_name',
        'device_token',
        'status',
        'last_used_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the unit associated with this device.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
