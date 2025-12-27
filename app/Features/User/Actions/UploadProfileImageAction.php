<?php

declare(strict_types=1);

namespace App\Features\User\Actions;

use App\Features\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadProfileImageAction
{
    public function handle(UploadedFile $image): User
    {
        /** @var User $user */
        $user = auth()->user();
        abort_if($user === null, 401, 'Unauthenticated');

        // Generate path for avatar: {user_id}/avatars/{uuid}.{extension}
        $extension = $image->getClientOriginalExtension();
        $path = $user->id.'/avatars/'.Str::uuid().'.'.$extension;

        // Upload to configured storage disk
        Storage::put($path, file_get_contents($image->getRealPath()));

        // Update user avatar
        $user->avatar = $path;
        $user->save();

        return $user;
    }
}
