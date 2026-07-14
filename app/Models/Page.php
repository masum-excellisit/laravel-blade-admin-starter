<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'body', 'meta_title', 'meta_description',
        'template', 'status', 'is_static',
    ];

    protected $casts = ['is_static' => 'boolean'];

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }
}
