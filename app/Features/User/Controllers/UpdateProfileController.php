<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\GetUserDataAction;
use App\Features\User\Actions\UpdateProfileAction;
use App\Features\User\Data\UpdateProfileData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UpdateProfileController extends Controller
{
    public function __construct(
        protected UpdateProfileAction $update_profile_action,
        protected GetUserDataAction $get_user_data_action
    ) {}

    public function __invoke(UpdateProfileData $data): JsonResponse
    {
        $user = $this->update_profile_action->handle($data);

        return response()->json($this->get_user_data_action->handle($user));
    }
}
