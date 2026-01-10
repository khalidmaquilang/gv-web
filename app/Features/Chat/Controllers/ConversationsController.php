<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\GetConversationsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationsController extends Controller
{
    public function __construct(protected GetConversationsAction $get_conversations_action) {}

    public function __invoke(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $conversations = $this->get_conversations_action->handle($page, $perPage);

        return response()->json([
            'data' => $conversations->items(),
            'current_page' => $conversations->currentPage(),
            'per_page' => $conversations->perPage(),
            'total' => $conversations->total(),
            'last_page' => $conversations->lastPage(),
        ]);
    }
}
