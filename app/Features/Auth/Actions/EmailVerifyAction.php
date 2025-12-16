<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\User\Models\User;
use Illuminate\Auth\Events\Verified;

class EmailVerifyAction
{
    public function handle(string $user_id): void
    {
        $user = User::find($user_id);
        if ($user === null) {
            abort(404);
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();

        event(new Verified($user));
    }
}
