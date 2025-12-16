<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\RequestPasswordAction;
use App\Features\Auth\Data\RequestPasswordData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RequestPasswordController extends Controller
{
    public function __construct(protected RequestPasswordAction $request_password_action) {}

    public function __invoke(RequestPasswordData $data): JsonResponse
    {
        $message = $this->request_password_action->handle($data);

        return response()->json(['message' => $message]);
    }
}
