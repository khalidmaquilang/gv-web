<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Features\Auth\Data\RequestPasswordData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class RequestPasswordAction
{
    public function handle(RequestPasswordData $data): string
    {
        $status = Password::sendResetLink(
            $data->toArray()
        );

        if ($status !== Password::ResetLinkSent) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}
