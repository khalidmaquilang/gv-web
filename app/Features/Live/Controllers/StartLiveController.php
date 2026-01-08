<?php

declare(strict_types=1);

namespace App\Features\Live\Controllers;

use App\Features\Live\Actions\StartLiveAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StartLiveController extends Controller
{
    public function __construct(protected StartLiveAction $start_live_action) {}

    public function __invoke(string $live_id): JsonResponse
    {
        $this->start_live_action->handle($live_id);

        return response()->json([
            'message' => 'success',
        ]);
    }
}
