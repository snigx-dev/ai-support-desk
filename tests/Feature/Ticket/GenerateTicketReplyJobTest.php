<?php

namespace Tests\Feature\Ticket;

use App\AI\Agents\TicketReplyAgent;
use App\AI\Agents\TicketAnalysisAgent;
use App\Actions\Tickets\CreateTicket;
use App\Jobs\AnalyzeTicketJob;
use App\Jobs\GenerateTicketReplyJob;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenerateTicketReplyJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dispatches_a_reply_job_after_ticket_analysis_completes(): void
    {
        Queue::fake();

        TicketAnalysisAgent::fake([
            [
                'summary' => 'The portal is down for sign in requests.',
                'priority' => 'urgent',
                'category' => 'bug',
            ],
        ])->preventStrayPrompts();

        $user = User::factory()->create();
        $ticket = app(CreateTicket::class)->handle($user, [
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
            'status' => 'open',
            'priority' => 'high',
        ]);

        app()->call([new AnalyzeTicketJob($ticket->getKey()), 'handle']);

        Queue::assertPushed(GenerateTicketReplyJob::class, function (GenerateTicketReplyJob $job) use ($ticket): bool {
            return $job->ticketId === $ticket->getKey();
        });
    }

    public function test_persists_ai_reply_and_confidence_on_the_ticket(): void
    {
        TicketReplyAgent::fake([
            [
                'reply' => 'Please refresh your session and let us know if the issue continues.',
                'confidence' => 0.88,
                'used_context_summary' => 'No similar historical tickets were needed.',
            ],
        ])->preventStrayPrompts();

        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        app()->call([new GenerateTicketReplyJob($ticket->getKey()), 'handle']);

        $ticket->refresh();

        $this->assertNotNull($ticket->ai_reply);
        $this->assertNotNull($ticket->ai_reply_confidence);
        $this->assertSame('Please refresh your session and let us know if the issue continues.', $ticket->ai_reply);
        $this->assertSame(0.88, $ticket->ai_reply_confidence);
    }
}
