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

        return $user->createToken(Str::random())->plainTextToken;
    }
}
