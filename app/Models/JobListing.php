<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JobListing extends Model
{
    protected $fillable = [
        'title', 'slug', 'location', 'employment_type', 'description',
        'requirements', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (JobListing $job) {
            if (blank($job->slug) && filled($job->title)) {
                $job->slug = Str::slug($job->title);
            }
        });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
