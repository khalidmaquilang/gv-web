<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Data\UpdateProfileData;
use App\Features\User\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateProfileAction
{
    public function handle(UpdateProfileData $data): User
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if($user === null, 401, 'Unauthenticated');

        // Check username uniqueness if it's being updated
        if ($data->username !== null && $data->username !== $user->username) {
            $exists = User::where('username', $data->username)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'username' => ['The username has already been taken.'],
                ]);
            }
        }

        // Update only provided fields
        if ($data->name !== null) {
            $user->name = $data->name;
        }

        if ($data->username !== null) {
            $user->username = $data->username;
        }

        if ($data->bio !== null) {
            $user->bio = $data->bio;
        }

        $user->save();

        return $user;
    }
}
