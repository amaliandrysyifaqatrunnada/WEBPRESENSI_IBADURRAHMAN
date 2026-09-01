<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'unit_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get unit relationship.
     */
    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the avatar URL for the user based on uploaded avatar or unit package type.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (\Illuminate\Support\Str::startsWith($this->avatar, ['http://', 'https://'])) {
                return $this->avatar;
            }
            if (file_exists(public_path('storage/' . $this->avatar)) || file_exists(storage_path('app/public/' . $this->avatar))) {
                return asset('storage/' . $this->avatar);
            }
            if (file_exists(public_path($this->avatar))) {
                return asset($this->avatar);
            }
        }

        // Fallback avatar based on unit's package type
        $packageType = $this->unit ? $this->unit->package_type : null;
        switch ($packageType) {
            case 'PAKET_A':
                return asset('images/packages/paket-a.jpg');
            case 'TK':
                return asset('images/packages/paket-tk.png');
            case 'PAKET_B':
                return asset('images/packages/paket-b.png');
            case 'PAKET_C':
                return asset('images/packages/paket-c.png');
            default:
                return asset('images/logo-pkbm-transparent.png');
        }
    }
}
