<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CmsContent extends Model
{
    protected $fillable = ['page', 'section', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getSection(string $page, string $section): array
    {
        return Cache::remember("cms.{$page}.{$section}", 300, function () use ($page, $section) {
            $row = static::query()->where('page', $page)->where('section', $section)->first();

            return $row?->data ?? [];
        });
    }

    public static function getField(string $page, string $section, string $field, mixed $default = null): mixed
    {
        $data = static::getSection($page, $section);

        return $data[$field] ?? $default;
    }

    public static function forgetPageCache(string $page): void
    {
        $sections = array_keys(config("cms.pages.{$page}.sections", []));
        foreach ($sections as $section) {
            Cache::forget("cms.{$page}.{$section}");
        }
    }

    public function imageUrl(?string $key = null): ?string
    {
        $path = $key ? ($this->data[$key] ?? null) : null;
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
