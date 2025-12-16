<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Data\LoginData;
use App\Features\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function handle(LoginData $data): string
    {
        $user = User::where('email', $data->email)
            ->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (app()->isProduction() && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            throw ValidationException::withMessages([
                'email' => ['You need to verify your email address. The verification link has been sent.'],
            ]);
        }

        if (! app()->isProduction() && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user->createToken(Str::random())->plainTextToken;
    }
}
