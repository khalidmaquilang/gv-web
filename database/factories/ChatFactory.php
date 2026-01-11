<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $conversation = Conversation::factory()->create(['type' => 'direct']);

        // Attach both users to the conversation
        $conversation->users()->attach([
            $sender->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
            $receiver->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now(),
            ],
        ]);

        return [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $this->faker->sentence(),
            'is_read' => false,
        ];
    }
}
