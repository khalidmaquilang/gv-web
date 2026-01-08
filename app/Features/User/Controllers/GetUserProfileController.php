<?php

declare(strict_types=1);

namespace App\Features\User\Controllers;

use App\Features\User\Actions\GetUserDataAction;
use App\Features\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GetUserProfileController extends Controller
{
    public function __construct(protected GetUserDataAction $get_user_data_action) {}

    public function __invoke(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        return response()->json($this->get_user_data_action->handle($user));
    }
}
