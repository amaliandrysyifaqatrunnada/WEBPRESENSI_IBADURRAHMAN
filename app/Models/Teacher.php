<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Teacher extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    protected $guard = 'teacher';

    protected $appends = ['display_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'name',
        'email',
        'password',
        'position',
        'phone',
        'avatar',
        'status',
        'unit_id',
        'face_registered',
        'face_registered_at',
        'face_template',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_template', // hide face template from serialization for safety
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'face_registered' => 'boolean',
            'face_registered_at' => 'datetime',
            'face_template' => 'encrypted:json',
        ];
    }

    /**
     * Configure Spatie Activity Log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nip', 'name', 'email', 'position', 'phone', 'status', 'unit_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Data pendidik '{$this->name}' telah {$eventName}");
    }

    /**
     * Get the unit that the teacher belongs to.
     */
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all attendances for the teacher.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all attendance logs for the teacher.
     */
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Get the formatted display ID for the teacher based on their unit/package type.
     */
    public function getDisplayIdAttribute(): string
    {
        $prefix = 'TCH';
        if ($this->unit_id) {
            $unit = $this->unit ?? \App\Models\Unit::find($this->unit_id);
            if ($unit) {
                $type = $unit->package_type;
                if ($type === 'PAKET_A') {
                    $prefix = 'TCH-A';
                } elseif ($type === 'PAKET_B') {
                    $prefix = 'TCH-B';
                } elseif ($type === 'PAKET_C') {
                    $prefix = 'TCH-C';
                } elseif ($type === 'TK') {
                    $prefix = 'TCH-TK';
                }
            }
        }
        return $prefix . '-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
