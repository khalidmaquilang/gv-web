<?php

declare(strict_types=1);

namespace App\Features\Auth\Data;

use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Data;

class ChangePasswordData extends Data
{
    public function __construct(
        public string $password,
        public string $token,
        public string $email,
    ) {}

    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ];
    }
}
