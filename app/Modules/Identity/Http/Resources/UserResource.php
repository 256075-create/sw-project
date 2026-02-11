<?php

namespace App\Modules\Identity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'mfa_enabled' => $this->mfa_enabled,
            'created_at' => $this->created_at?->toISOString(),
            'last_login' => $this->last_login?->toISOString(),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(fn ($role) => [
                    'role_id' => $role->role_id,
                    'role_name' => $role->role_name,
                ]);
            }),
            'permissions' => $this->getAllPermissions(),
        ];
    }
}
