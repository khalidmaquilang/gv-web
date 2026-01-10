<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Features\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => 'direct',
            'name' => null,
        ];
    }
}
