<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Data\ChangePasswordData;
use App\Features\User\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    public function handle(ChangePasswordData $data): void
    {
        $status = Password::reset(
            $data->toArray(),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return;
        }

        throw ValidationException::withMessages([
            'password' => [__($status)],
        ]);
    }
}
