<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\RegisterAction;
use App\Features\Auth\Data\RegisterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(protected RegisterAction $register_action) {}

    public function __invoke(RegisterData $data): JsonResponse
    {
        $this->register_action->handle($data);

        return response()->json(['message' => 'Please check your email for verification link.']);
    }
}
