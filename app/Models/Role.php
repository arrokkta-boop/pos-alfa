<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const KASIR = 1;
    public const GUDANG = 2;
    public const ADMIN = 3;

    protected $fillable = [
        'role_name',
    ];

    /**
     * Relationship to Users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
