<?php

namespace Tests\Unit\Services\AI;

use App\AI\Agents\TicketAnalysisAgent;
use App\Data\Tickets\TicketAnalysisInputData;
use App\Data\Tickets\TicketAnalysisData;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Models\Ticket;
use App\Services\AI\TicketAnalyzer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class TicketAnalyzerTest extends TestCase
{
    use DatabaseMigrations;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_logs_and_uses_fallback_when_the_ai_provider_fails(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket analysis AI request failed.'
                    && $context['ticket_id'] !== null
                    && $context['exception'] === \RuntimeException::class;
            });

        $logger->shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket analysis fallback used.'
                    && $context['fallback_priority'] === TicketPriority::Urgent->value
                    && $context['fallback_category'] === TicketCategory::Bug->label();
            });

        app()->instance(LoggerInterface::class, $logger);

        TicketAnalysisAgent::fake(function () {
            throw new \RuntimeException('provider unavailable');
        })->preventStrayPrompts();

        $analysis = app(TicketAnalyzer::class)->analyze(
            TicketAnalysisInputData::fromTicket($ticket),
        );

        $this->assertInstanceOf(TicketAnalysisData::class, $analysis);
        $this->assertSame(TicketPriority::Urgent, $analysis->priority);
        $this->assertSame(TicketCategory::Bug, $analysis->category);
        $this->assertSame('bug', $analysis->categoryLabel);
        $this->assertStringContainsString('Fallback analysis for', $analysis->summary);
    }

    public function test_logs_and_uses_fallback_when_the_ai_response_is_invalid(): void
    {
        $ticket = Ticket::factory()->create([
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket analysis AI response was invalid.'
                    && $context['ticket_id'] !== null
                    && $context['exception'] === \App\Exceptions\TicketAnalysisException::class;
            });

        $logger->shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Ticket analysis fallback used.'
                    && $context['fallback_priority'] === TicketPriority::Urgent->value
                    && $context['fallback_category'] === TicketCategory::Bug->label();
            });

        app()->instance(LoggerInterface::class, $logger);

        TicketAnalysisAgent::fake([
            [
                'summary' => 'The user cannot sign in.',
                'priority' => 'invalid',
                'category' => 'unknown-category',
            ],
        ])->preventStrayPrompts();

        $analysis = app(TicketAnalyzer::class)->analyze(
            TicketAnalysisInputData::fromTicket($ticket),
        );

        $this->assertSame(TicketPriority::Urgent, $analysis->priority);
        $this->assertSame(TicketCategory::Bug, $analysis->category);
        $this->assertSame('bug', $analysis->categoryLabel);
    }

    public function test_maps_structured_ai_output_to_enums_safely(): void
    {
        TicketAnalysisAgent::fake([
            [
                'summary' => 'Customer cannot sign in to the dashboard.',
                'priority' => 'high',
                'category' => 'login',
            ],
        ])->preventStrayPrompts();

        $ticket = Ticket::factory()->create([
            'title' => 'Login failure',
            'message' => 'I cannot sign in to the dashboard.',
        ]);

        $analysis = app(TicketAnalyzer::class)->analyze(
            TicketAnalysisInputData::fromTicket($ticket),
        );

        $this->assertSame('Customer cannot sign in to the dashboard.', $analysis->summary);
        $this->assertSame(TicketPriority::High, $analysis->priority);
        $this->assertSame(TicketCategory::Access, $analysis->category);
        $this->assertSame('access', $analysis->categoryLabel);
    }

    public function test_preserves_unknown_categories_as_dynamic_labels(): void
    {
        TicketAnalysisAgent::fake([
            [
                'summary' => 'Customer reports a custom workflow issue.',
                'priority' => 'medium',
                'category' => 'custom workflow',
            ],
        ])->preventStrayPrompts();

        $ticket = Ticket::factory()->create([
            'title' => 'Workflow issue',
            'message' => 'The customer has a custom workflow issue.',
        ]);

        $analysis = app(TicketAnalyzer::class)->analyze(
            TicketAnalysisInputData::fromTicket($ticket),
        );

        $this->assertSame(TicketCategory::Other, $analysis->category);
        $this->assertSame('custom workflow', $analysis->categoryLabel);
    }
}
