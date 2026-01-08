<?php

declare(strict_types=1);

namespace App\Features\Live\Controllers;

use App\Features\Live\Actions\EndLiveAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EndLiveController extends Controller
{
    public function __construct(protected EndLiveAction $end_live_action) {}

    public function __invoke(string $live_id): JsonResponse
    {
        $this->end_live_action->handle($live_id);

        return response()->json([
            'message' => 'success',
        ]);
    }
}
