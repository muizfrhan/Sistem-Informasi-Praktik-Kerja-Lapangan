<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value', 'group', 'type', 'label'];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('sipkl.settings'));
        static::deleted(fn() => Cache::forget('sipkl.settings'));
    }

    /** Ambil nilai setting dengan tipe yang sesuai. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('sipkl.settings', function () {
            return static::query()->pluck('value', 'key')->all();
        });

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        $row = static::where('key', $key)->first();
        $value = $all[$key];

        return match ($row?->type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int' => (int) $value,
            'json' => json_decode($value, true) ?? $default,
            default => $value,
        };
    }

    public static function put(string $key, mixed $value, string $group = 'umum', string $type = 'string', ?string $label = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'group' => $group,
                'type' => $type,
                'label' => $label,
            ]
        );
    }
}
