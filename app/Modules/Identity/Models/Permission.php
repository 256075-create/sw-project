<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'identity_permissions';
    protected $primaryKey = 'permission_id';
    public $timestamps = false;

    protected $fillable = [
        'permission_name',
        'resource',
        'action',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'identity_role_permission',
            'permission_id',
            'role_id'
        );
    }
}
