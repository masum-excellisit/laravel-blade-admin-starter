<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    protected $fillable = [
        'title', 'slug', 'client', 'excerpt', 'body', 'image', 'project_url',
        'sort_order', 'status', 'published_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }
}
