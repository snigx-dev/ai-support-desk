<?php

namespace App\Services\AI;

use App\Exceptions\TicketEmbeddingException;
use App\Models\Ticket;
use App\Models\TicketEmbedding;
use Laravel\Ai\Embeddings;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class EmbeddingGenerator
{
    private const int EMBEDDING_DIMENSIONS = 1536;

    public function __construct(
        private TicketEmbedding $ticketEmbedding,
        private LoggerInterface $logger,
    ) {
    }

    public function generate(Ticket $ticket): ?TicketEmbedding
    {
        $content = $this->contentForTicket($ticket);
        $contentHash = hash('sha256', $content);

        try {
            $existing = $this->ticketEmbedding->newQuery()
                ->where('ticket_id', $ticket->getKey())
                ->first();
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket embedding lookup failed; skipping generation.', [
                'ticket_id' => $ticket->getKey(),
                'title' => $ticket->title,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($existing !== null && $existing->content_hash === $contentHash) {
            return $existing;
        }

        try {
            $response = Embeddings::for([$content])
                ->dimensions(self::EMBEDDING_DIMENSIONS)
                ->generate();
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket embedding generation failed.', [
                'ticket_id' => $ticket->getKey(),
                'title' => $ticket->title,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw TicketEmbeddingException::generationFailed(
                sprintf('Unable to generate embedding for ticket %d.', $ticket->getKey()),
                $exception,
            );
        }

        $embedding = $response->first();

        try {
            return $this->ticketEmbedding->newQuery()->updateOrCreate(
                ['ticket_id' => $ticket->getKey()],
                [
                    'content' => $content,
                    'content_hash' => $contentHash,
                    'embedding' => $embedding,
                ],
            );
        } catch (Throwable $exception) {
            $this->logger->warning('Ticket embedding persistence failed; skipping save.', [
                'ticket_id' => $ticket->getKey(),
                'title' => $ticket->title,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public function contentForTicket(Ticket $ticket): string
    {
        return trim(implode("\n\n", array_filter([
            'Title: '.$ticket->title,
            'Message: '.$ticket->message,
        ], static fn (string $value): bool => $value !== '')));
    }
}
