<?php

namespace App\Models;

use App\Models\Concerns\HasRevisions;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasRevisions;

    protected $fillable = [
        'title', 'slug', 'body', 'meta_title', 'meta_description',
        'og_image', 'canonical_url', 'template', 'status', 'is_static',
    ];

    protected $casts = ['is_static' => 'boolean'];

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }
}
