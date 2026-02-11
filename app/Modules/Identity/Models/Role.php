<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'identity_roles';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'identity_user_role',
            'role_id',
            'user_id'
        )->withPivot('assigned_at');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'identity_role_permission',
            'role_id',
            'permission_id'
        );
    }
}
