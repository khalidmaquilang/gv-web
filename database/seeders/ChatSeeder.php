<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Features\Chat\Models\Chat;
use App\Features\Chat\Models\Conversation;
use App\Features\User\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create test users
        $user1 = User::firstOrCreate(
            ['email' => 'test1@example.com'],
            [
                'name' => 'Test User 1',
                'password' => bcrypt('password'),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Test User 2',
                'password' => bcrypt('password'),
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'test3@example.com'],
            [
                'name' => 'Test User 3',
                'password' => bcrypt('password'),
            ]
        );

        // Create conversations with messages
        $this->createConversationWithMessages($user1, $user2, 15);
        $this->createConversationWithMessages($user1, $user3, 25);
        $this->createConversationWithMessages($user2, $user3, 10);

        $this->command->info('Chat seeder completed! Created 3 conversations with messages.');
    }

    private function createConversationWithMessages(User $user1, User $user2, int $messageCount): void
    {
        // Create conversation
        $conversation = Conversation::create(['type' => 'direct']);

        // Attach both users
        $conversation->users()->attach([
            $user1->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now()->subDays(rand(1, 30)),
            ],
            $user2->id => [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'joined_at' => now()->subDays(rand(1, 30)),
            ],
        ]);

        // Create messages
        for ($i = 0; $i < $messageCount; $i++) {
            $sender = $i % 2 === 0 ? $user1 : $user2;
            $receiver = $i % 2 === 0 ? $user2 : $user1;

            Chat::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => "Test message {$i} from {$sender->name}",
                'is_read' => $i < ($messageCount - rand(0, 5)), // Some unread
                'created_at' => now()->subMinutes($messageCount - $i),
            ]);
        }

        // Update conversation timestamp
        $conversation->touch();
    }
}
