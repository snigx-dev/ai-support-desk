<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketEmbedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketEmbedding>
 */
class TicketEmbeddingFactory extends Factory
{
    protected $model = TicketEmbedding::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'content' => fake()->paragraph(),
            'content_hash' => hash('sha256', fake()->sentence()),
            'embedding' => array_fill(0, 1536, 0.1),
        ];
    }
}
