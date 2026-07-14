<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    protected $fillable = ['menu_id', 'parent_id', 'label', 'type', 'value', 'order'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function url(): string
    {
        return match ($this->type) {
            'page' => url('/'.ltrim((string) $this->value, '/')),
            'route' => Route::has($this->value) ? route($this->value) : '#',
            default => $this->value ?: '#',
        };
    }
}
