<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'identity_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'email',
        'password_hash',
        'is_active',
        'mfa_enabled',
        'last_login',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'created_at' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['user_id'];
    }

    protected static function newFactory()
    {
        return \App\Modules\Identity\Database\Factories\UserFactory::new();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'identity_user_role',
            'user_id',
            'role_id'
        )->withPivot('assigned_at');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'user_id', 'user_id');
    }

    public function getAllPermissions(): array
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('permission_name')
            ->unique()
            ->values()
            ->toArray();
    }
}
