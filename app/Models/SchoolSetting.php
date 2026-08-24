<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'unit_id',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null, ?int $unitId = null)
    {
        if ($unitId !== null) {
            $setting = self::where('key', $key)->where('unit_id', $unitId)->first();
            if ($setting) {
                return $setting->value;
            }
        }
        $setting = self::where('key', $key)->whereNull('unit_id')->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, ?int $unitId = null): self
    {
        return self::updateOrCreate(
            ['key' => $key, 'unit_id' => $unitId],
            ['value' => $value]
        );
    }
}
