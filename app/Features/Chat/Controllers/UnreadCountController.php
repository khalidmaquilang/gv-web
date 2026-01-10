<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\GetUnreadCountAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UnreadCountController extends Controller
{
    public function __construct(protected GetUnreadCountAction $get_unread_count_action) {}

    public function __invoke(): JsonResponse
    {
        $count = $this->get_unread_count_action->handle();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
