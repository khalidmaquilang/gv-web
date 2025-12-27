<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\UploadProfileImageAction;
use App\Features\User\Data\UploadProfileImageData;
use App\Features\User\Data\UserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UploadProfileImageController extends Controller
{
    public function __construct(protected UploadProfileImageAction $upload_profile_image_action) {}

    public function __invoke(UploadProfileImageData $data): JsonResponse
    {
        $user = $this->upload_profile_image_action->handle($data->image);

        return response()->json(UserData::from($user));
    }
}
