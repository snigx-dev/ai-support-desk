<?php

namespace Tests\Unit\Services\AI;

use App\AI\Agents\TicketReplyAgent;
use App\Data\Tickets\TicketReplyData;
use App\Models\Ticket;
use App\Models\TicketEmbedding;
use App\Services\AI\TicketReplyGenerator;
use App\Services\Search\SimilarTicketFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Laravel\Ai\Prompts\AgentPrompt;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class TicketReplyGeneratorTest extends TestCase
{
    use DatabaseMigrations;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_produces_a_valid_reply_dto_using_similar_ticket_context(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        $similarTicket = Ticket::factory()->create([
            'title' => 'Login error',
            'message' => 'Users see a 500 error after signing in.',
            'ai_summary' => 'We restored the authentication service and cleared the cache.',
            'ai_reply' => 'We restored the authentication service and cleared the cache.',
        ]);

        $similarFinder = $this->finderReturning(collect([$this->makeEmbeddingResult($similarTicket)]));

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('warning');
        $logger->shouldNotReceive('info');

        app()->instance(SimilarTicketFinder::class, $similarFinder);
        app()->instance(LoggerInterface::class, $logger);

        TicketReplyAgent::fake([
            [
                'reply' => 'Please retry after clearing your session and let us know if the error continues.',
                'confidence' => 0.91,
                'used_context_summary' => 'Similar tickets indicated an authentication outage resolution.',
            ],
        ])->preventStrayPrompts();

        $reply = app(TicketReplyGenerator::class)->generate($ticket);

        $this->assertInstanceOf(TicketReplyData::class, $reply);
        $this->assertSame('Please retry after clearing your session and let us know if the error continues.', $reply->reply);
        $this->assertSame(0.91, $reply->confidence);
        $this->assertSame('Similar tickets indicated an authentication outage resolution.', $reply->usedContextSummary);

        TicketReplyAgent::assertPrompted(function (AgentPrompt $prompt) use ($ticket, $similarTicket): bool {
            return $prompt->contains($ticket->title)
                && $prompt->contains($ticket->message)
                && $prompt->contains($similarTicket->title)
                && $prompt->contains($similarTicket->ai_summary)
                && $prompt->contains($similarTicket->ai_reply);
        });
    }

    public function test_falls_back_when_the_ai_provider_fails(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Billing issue',
            'message' => 'I was charged twice for the same invoice.',
        ]);

        $similarFinder = $this->finderReturning(collect());

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket reply AI request failed.'
                    && isset($context['ticket_id'])
                    && $context['exception'] === \RuntimeException::class;
            });
        $logger->shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket reply fallback used.'
                    && $context['reason'] === 'request failed';
            });

        app()->instance(SimilarTicketFinder::class, $similarFinder);
        app()->instance(LoggerInterface::class, $logger);

        TicketReplyAgent::fake(function () {
            throw new \RuntimeException('provider unavailable');
        })->preventStrayPrompts();

        $reply = app(TicketReplyGenerator::class)->generate($ticket);

        $this->assertSame('We are reviewing your request and will respond shortly.', $reply->reply);
        $this->assertSame(0.1, $reply->confidence);
        $this->assertStringContainsString('no similar historical tickets', strtolower($reply->usedContextSummary));
    }

    public function test_falls_back_when_the_ai_response_confidence_is_invalid(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Login error',
            'message' => 'Users see a 500 error after signing in.',
        ]);

        $similarFinder = $this->finderReturning(collect());

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket reply AI response was invalid.'
                    && $context['exception'] === \App\Exceptions\TicketReplyException::class;
            });
        $logger->shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket reply fallback used.'
                    && $context['reason'] === 'invalid response';
            });

        app()->instance(SimilarTicketFinder::class, $similarFinder);
        app()->instance(LoggerInterface::class, $logger);

        TicketReplyAgent::fake([
            [
                'reply' => 'Please clear your browser session and retry.',
                'confidence' => 1.5,
                'used_context_summary' => 'The agent used login-related ticket history.',
            ],
        ])->preventStrayPrompts();

        $reply = app(TicketReplyGenerator::class)->generate($ticket);

        $this->assertSame('We are reviewing your request and will respond shortly.', $reply->reply);
        $this->assertSame(0.1, $reply->confidence);
    }

    private function finderReturning(Collection $similarTickets): SimilarTicketFinder
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('getDriverName')->andReturn('sqlite');

        $ticketEmbeddingModel = Mockery::mock(TicketEmbedding::class)->makePartial();
        $ticketEmbeddingModel->shouldReceive('getConnection')->andReturn($connection);

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('with')->with('ticket')->andReturnSelf();
        $builder->shouldReceive('where')->with('ticket_id', '!=', Mockery::any())->andReturnSelf();
        $builder->shouldReceive('get')->andReturn($similarTickets);
        $ticketEmbeddingModel->shouldReceive('newQuery')->andReturn($builder);

        return new SimilarTicketFinder(
            $ticketEmbeddingModel,
            new \App\Services\AI\EmbeddingGenerator(new TicketEmbedding, Mockery::mock(LoggerInterface::class)),
            Mockery::mock(LoggerInterface::class),
        );
    }

    private function makeEmbeddingResult(Ticket $ticket): TicketEmbedding
    {
        $embedding = TicketEmbedding::factory()->make([
            'ticket_id' => $ticket->getKey(),
            'embedding' => [1.0, 0.0],
        ]);
        $embedding->setRelation('ticket', $ticket);

        return $embedding;
    }
}
