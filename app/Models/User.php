<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'username',
        'password',
        'name',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to Role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship to Sales processed by this cashier.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    /**
     * Relationship to StockLogs recorded by this user.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class, 'user_id');
    }

    /**
     * Role checking helpers.
     */
    public function isKasir(): bool
    {
        return $this->role_id === Role::KASIR || $this->role?->role_name === 'UKasir';
    }

    public function isGudang(): bool
    {
        return $this->role_id === Role::GUDANG || $this->role?->role_name === 'UGudang';
    }

    public function isAdmin(): bool
    {
        return $this->role_id === Role::ADMIN || $this->role?->role_name === 'UAdmin';
    }
}
