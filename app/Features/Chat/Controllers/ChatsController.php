<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\GetChatsAction;
use App\Features\Chat\Data\ChatData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ChatsController extends Controller
{
    public function __construct(protected GetChatsAction $get_chats_action) {}

    public function __invoke(string $user_id): JsonResponse
    {
        $chats = $this->get_chats_action->handle($user_id);

        return response()->json(ChatData::collect($chats));
    }
}
