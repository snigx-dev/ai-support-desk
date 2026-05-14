<?php

namespace App\Services\AI;

use App\AI\Agents\TicketAnalysisAgent;
use App\Data\Tickets\TicketAnalysisInputData;
use App\Data\Tickets\TicketAnalysisData;
use App\Exceptions\TicketAnalysisException;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class TicketAnalyzer
{
    public function __construct(
        private TicketAnalysisAgent $agent,
        private TicketAnalysisFallbackAnalyzer $fallbackAnalyzer,
        private LoggerInterface     $logger,
    ) {
    }

    public function analyze(TicketAnalysisInputData $input): TicketAnalysisData
    {
        try {
            $response = $this->agent->prompt($input->toPrompt());
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket analysis AI request failed.', [
                ...$input->toLogContext(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($input, $exception, 'request failed');
        }

        try {
            if (! $response instanceof StructuredAgentResponse) {
                throw TicketAnalysisException::invalidResponse('Ticket analysis agent did not return structured output.');
            }

            return TicketAnalysisData::fromStructuredResponse($response);
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket analysis AI response was invalid.', [
                ...$input->toLogContext(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($input, $exception, 'invalid response');
        }
    }

    private function fallback(
        TicketAnalysisInputData $input,
        Throwable $exception,
        string $reason,
    ): TicketAnalysisData {
        $fallback = $this->fallbackAnalyzer->analyze($input)->withFallbackSource('ai-fallback');

        $this->logger->info('Ticket analysis fallback used.', [
            ...$input->toLogContext(),
            'exception' => $exception::class,
            'reason' => $reason,
            'fallback_priority' => $fallback->priority->value,
            'fallback_category' => $fallback->categoryLabel,
        ]);

        return $fallback;
    }
}
