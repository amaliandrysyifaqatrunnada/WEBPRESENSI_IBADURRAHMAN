<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrTokenUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_hash',
        'unit_id',
        'teacher_id',
        'attendance_type',
        'latitude',
        'longitude',
        'accuracy',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
