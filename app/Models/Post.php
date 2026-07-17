<?php

namespace App\Models;

use App\Models\Concerns\HasRevisions;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasRevisions;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'featured_image',
        'category_id', 'author_id', 'meta_title', 'meta_description',
        'og_image', 'canonical_url', 'status', 'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }
}
