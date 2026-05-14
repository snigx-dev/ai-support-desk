<?php

namespace Tests\Feature\Ticket;

use App\Actions\Tickets\CreateTicket;
use App\Jobs\GenerateTicketEmbeddingJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Tests\TestCase;

class TicketEmbeddingPipelineTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dispatches_an_embedding_job_when_a_ticket_is_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        app(CreateTicket::class)->handle($user, [
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        Queue::assertPushed(GenerateTicketEmbeddingJob::class);
    }

    public function test_creates_a_ticket_embedding_from_the_ticket_title_and_message(): void
    {
        Embeddings::fake(function (EmbeddingsPrompt $prompt): array {
            return array_map(
                fn (): array => Embeddings::fakeEmbedding($prompt->dimensions),
                $prompt->inputs,
            );
        })->preventStrayEmbeddings();

        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        app()->call([new GenerateTicketEmbeddingJob($ticket->getKey()), 'handle']);

        $ticket->refresh();
        $embedding = $ticket->embedding()->first();

        $this->assertNotNull($embedding);
        $this->assertSame($ticket->getKey(), $embedding->ticket_id);
        $this->assertStringContainsString($ticket->title, $embedding->content);
        $this->assertStringContainsString($ticket->message, $embedding->content);
        $this->assertNotEmpty($embedding->embedding);

        Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) use ($ticket): bool {
            return $prompt->contains($ticket->title)
                && $prompt->contains($ticket->message)
                && $prompt->dimensions === 1536;
        });
    }
}
