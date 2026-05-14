<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(3, true),
            'status' => TicketStatus::Open,
            'priority' => null,
            'ai_summary' => null,
            'ai_category' => null,
            'ai_raw_response' => null,
            'ai_reply' => null,
            'ai_reply_confidence' => null,
        ];
    }
}
