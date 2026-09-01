<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PosSetting extends Model
{
    protected $table = 'pos_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getString(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('pos_settings')) {
            return $default;
        }

        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::getString($key, null);
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    public static function setString(string $key, ?string $value): void
    {
        if (! Schema::hasTable('pos_settings')) {
            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}

