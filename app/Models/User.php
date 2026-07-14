<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const TYPE_ADMIN = 'admin';

    public const TYPE_CUSTOMER = 'customer';

    protected $fillable = ['name', 'email', 'type', 'password', 'avatar', 'phone', 'status', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    public function scopeAdmins($q)
    {
        return $q->where('type', self::TYPE_ADMIN);
    }

    public function scopeCustomers($q)
    {
        return $q->where('type', self::TYPE_CUSTOMER);
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this->type === self::TYPE_CUSTOMER;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::disk('public')->url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=fff';
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->take(2)->map(fn ($p) => Str::substr($p, 0, 1))->implode('');
    }
}
