<?php

use App\Features\Chat\Controllers\ChatsController;
use App\Features\Chat\Controllers\MarkChatAsReadController;
use App\Features\Chat\Controllers\SendChatMessageController;
use App\Features\Chat\Controllers\UnreadCountController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Get chat messages with a specific user
    Route::get('/chats/{user_id}', ChatsController::class);

    // Send a chat message
    Route::post('/chats', SendChatMessageController::class);

    // Mark a chat message as read
    Route::post('/chats/{chat_id}/read', MarkChatAsReadController::class);

    // Get unread messages count
    Route::get('/chats/unread/count', UnreadCountController::class);
});
