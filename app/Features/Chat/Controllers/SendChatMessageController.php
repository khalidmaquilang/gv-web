<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\SendChatMessageAction;
use App\Features\Chat\Data\SendChatMessageData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SendChatMessageController extends Controller
{
    public function __construct(protected SendChatMessageAction $send_chat_message_action) {}

    public function __invoke(SendChatMessageData $data): JsonResponse
    {
        $id = $this->send_chat_message_action->handle($data);

        return response()->json([
            'message' => 'success',
            'id' => $id,
        ]);
    }
}
