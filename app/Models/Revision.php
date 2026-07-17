<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $fillable = [
        'revisionable_type', 'revisionable_id', 'user_id', 'payload', 'note',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function revisionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
