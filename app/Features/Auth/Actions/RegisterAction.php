<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Data\RegisterData;
use App\Features\User\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisterAction
{
    public function handle(RegisterData $data): void
    {
        $user = User::create($data->toArray());

        event(new Registered($user));
    }
}
