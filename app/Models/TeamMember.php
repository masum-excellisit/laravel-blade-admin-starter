<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'name', 'role_title', 'bio', 'photo', 'email', 'linkedin', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
