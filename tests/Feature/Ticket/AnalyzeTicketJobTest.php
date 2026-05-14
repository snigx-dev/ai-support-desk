<?php

namespace Tests\Feature\Ticket;

use App\AI\Agents\TicketAnalysisAgent;
use App\Actions\Tickets\CreateTicket;
use App\Enums\TicketPriority;
use App\Jobs\AnalyzeTicketJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalyzeTicketJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dispatches_an_analysis_job_when_a_ticket_is_created(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $ticket = app(CreateTicket::class)->handle($user, [
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        Queue::assertPushed(AnalyzeTicketJob::class, function (AnalyzeTicketJob $job) use ($ticket): bool {
            return $job->ticketId === $ticket->getKey();
        });
    }

    public function test_updates_the_ticket_with_structured_ai_output(): void
    {
        TicketAnalysisAgent::fake([
            [
                'summary' => 'The user cannot sign in because the portal is failing with a server error.',
                'priority' => 'urgent',
                'category' => 'access',
            ],
        ])->preventStrayPrompts();

        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        $job = new AnalyzeTicketJob($ticket->getKey());

        app()->call([$job, 'handle']);

        $ticket->refresh();

        $this->assertSame('The user cannot sign in because the portal is failing with a server error.', $ticket->ai_summary);
        $this->assertSame(TicketPriority::Urgent, $ticket->priority);
        $this->assertSame('access', $ticket->ai_category);
        $this->assertNotNull($ticket->ai_raw_response);

        TicketAnalysisAgent::assertPrompted(function ($prompt) use ($ticket): bool {
            return $prompt->contains($ticket->title) && $prompt->contains($ticket->message);
        });
    }

    public function test_uses_a_stable_unique_id_and_retry_backoff_strategy(): void
    {
        $job = new AnalyzeTicketJob(42);

        $this->assertSame('ticket-analysis:42', $job->uniqueId());
        $this->assertSame([1, 5, 10], $job->backoff());
        $this->assertSame(3, $job->tries);
    }
}
