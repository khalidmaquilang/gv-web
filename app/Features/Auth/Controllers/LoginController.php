<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\LoginAction;
use App\Features\Auth\Data\LoginData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(protected LoginAction $login_action) {}

    public function __invoke(LoginData $data): JsonResponse
    {
        $token = $this->login_action->handle($data);

        return response()->json(['token' => $token]);
    }
}
