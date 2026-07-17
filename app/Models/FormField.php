<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_id', 'label', 'name', 'type', 'options', 'required', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
