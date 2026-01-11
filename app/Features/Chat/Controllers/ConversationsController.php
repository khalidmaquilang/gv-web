<?php

declare(strict_types=1);

namespace App\Features\Chat\Controllers;

use App\Features\Chat\Actions\GetConversationsAction;
use App\Features\Chat\Data\ConversationData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConversationsController extends Controller
{
    public function __construct(protected GetConversationsAction $get_conversations_action) {}

    public function __invoke(): JsonResponse
    {
        $conversations = $this->get_conversations_action->handle();

        return response()->json(ConversationData::collect($conversations));
    }
}
