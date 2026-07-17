<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'from_path', 'to_url', 'status_code', 'is_active', 'hits',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
        'hits' => 'integer',
    ];
}
