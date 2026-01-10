<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\MarkChatAsReadAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MarkChatAsReadController extends Controller
{
    public function __construct(protected MarkChatAsReadAction $mark_chat_as_read_action) {}

    public function __invoke(string $chat_id): JsonResponse
    {
        $this->mark_chat_as_read_action->handle($chat_id);

        return response()->json([
            'message' => 'success',
        ]);
    }
}
