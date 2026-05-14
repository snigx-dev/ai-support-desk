<?php

namespace App\Services\AI;

use App\AI\Agents\TicketReplyAgent;
use App\AI\Prompts\TicketReplyPrompt;
use App\Data\Tickets\TicketReplyData;
use App\Exceptions\TicketReplyException;
use App\Models\Ticket;
use App\Services\Search\SimilarTicketFinder;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class TicketReplyGenerator
{
    public function __construct(
        private SimilarTicketFinder $similarTicketFinder,
        private TicketReplyAgent $agent,
        private LoggerInterface $logger,
    ) {
    }

    public function generate(Ticket $ticket): TicketReplyData
    {
        try {
            $prompt = $this->buildPrompt($ticket);
            $response = $this->agent->prompt($prompt->toPrompt());
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket reply AI request failed.', [
                'ticket_id' => $ticket->getKey(),
                'title' => $ticket->title,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($ticket->getKey(), $ticket->title, $exception, 'request failed');
        }

        try {
            if (! $response instanceof StructuredAgentResponse) {
                throw TicketReplyException::invalidResponse('Ticket reply agent did not return structured output.');
            }

            return TicketReplyData::fromStructuredResponse($response);
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket reply AI response was invalid.', [
                'ticket_id' => $ticket->getKey(),
                'title' => $ticket->title,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->fallback($ticket->getKey(), $ticket->title, $exception, 'invalid response');
        }
    }

    private function buildPrompt(Ticket $ticket): TicketReplyPrompt
    {
        return TicketReplyPrompt::forTicket(
            $ticket,
            $this->similarTicketFinder->findSimilar($ticket),
        );
    }

    private function fallback(
        int $ticketId,
        string $ticketTitle,
        Throwable $exception,
        string $reason,
    ): TicketReplyData {
        $fallback = TicketReplyData::fallback(sprintf(
            'Current ticket only: %s; no similar historical tickets were found.',
            $ticketTitle,
        ));

        $this->logger->info('Ticket reply fallback used.', [
            'ticket_id' => $ticketId,
            'reason' => $reason,
            'exception' => $exception::class,
            'confidence' => $fallback->confidence,
        ]);

        return $fallback;
    }
}
