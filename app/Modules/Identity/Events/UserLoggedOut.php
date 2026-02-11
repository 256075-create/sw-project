<?php

namespace App\Modules\Identity\Events;

use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedOut
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user
    ) {}
}
