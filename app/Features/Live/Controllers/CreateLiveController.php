<?php

declare(strict_types=1);

namespace App\Features\Live\Controllers;

use App\Features\Live\Actions\CreateLiveAction;
use App\Features\Live\Data\CreateLiveData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CreateLiveController extends Controller
{
    public function __construct(protected CreateLiveAction $create_live_action) {}

    public function __invoke(CreateLiveData $request): JsonResponse
    {
        $id = $this->create_live_action->handle($request);

        return response()->json([
            'id' => $id,
            'message' => 'success',
        ]);
    }
}
