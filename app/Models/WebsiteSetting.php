<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebsiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    /**
     * Get a single setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    /**
     * Set a single setting value by key. Creates if missing.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
    }

    /**
     * Get all settings for a group as key => value array (short key without group prefix).
     */
    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->pluck('value', 'key')->all();

        $result = [];
        foreach ($settings as $fullKey => $value) {
            // Strip group prefix: "hero.title" -> "title"
            $shortKey = Str::after($fullKey, $group . '.');
            $result[$shortKey] = $value;
        }

        return $result;
    }

    /**
     * Get all settings as grouped array: [group => [short_key => value]]
     */
    public static function getAllGrouped(): array
    {
        $settings = static::orderBy('group')->orderBy('key')->get();

        $grouped = [];
        foreach ($settings as $setting) {
            $shortKey = Str::after($setting->key, $setting->group . '.');
            $grouped[$setting->group][$shortKey] = $setting->value;
        }

        return $grouped;
    }
}
