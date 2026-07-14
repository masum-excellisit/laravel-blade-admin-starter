<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function get(string $key, $default = null)
    {
        return static::all_cached()[$key] ?? $default;
    }

    public static function all_cached(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::pluck('value', 'key')->toArray());
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    public static function put(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group, 'type' => $type]);
        Cache::forget('settings.all');
    }

    public static function flush(): void
    {
        Cache::forget('settings.all');
    }
}
