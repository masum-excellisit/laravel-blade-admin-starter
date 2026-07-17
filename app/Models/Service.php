<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'icon', 'image', 'sort_order', 'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if (blank($service->slug) && filled($service->title)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
