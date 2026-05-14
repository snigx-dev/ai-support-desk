<?php

namespace App\Services\Search;

use App\Models\TicketEmbedding;
use App\Services\AI\EmbeddingGenerator;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Laravel\Ai\Embeddings;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class SimilarTicketFinder
{
    public function __construct(
        private TicketEmbedding $ticketEmbedding,
        private EmbeddingGenerator $embeddingGenerator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return SupportCollection<int, Ticket>
     */
    public function findSimilar(Ticket $ticket): SupportCollection
    {
        $queryContent = $this->embeddingGenerator->contentForTicket($ticket);

        try {
            if ($this->supportsVectorQueries()) {
                /** @var EloquentCollection<int, TicketEmbedding> $similarEmbeddings */
                $similarEmbeddings = $this->ticketEmbedding->newQuery()
                    ->with('ticket')
                    ->where('ticket_id', '!=', $ticket->getKey())
                    ->whereVectorSimilarTo('embedding', $queryContent, 0.6)
                    ->limit(5)
                    ->get();

                return $similarEmbeddings
                    ->map(fn (TicketEmbedding $ticketEmbedding): ?Ticket => $ticketEmbedding->ticket)
                    ->filter()
                    ->values();
            }

            $queryEmbedding = collect(Embeddings::for([$queryContent])
                ->generate()
                ->first());

            return $this->ticketEmbedding->newQuery()
                ->with('ticket')
                ->where('ticket_id', '!=', $ticket->getKey())
                ->get()
                ->map(function (TicketEmbedding $ticketEmbedding) use ($queryEmbedding): array {
                    $candidateEmbedding = collect($ticketEmbedding->embedding);

                    return [
                        'ticket' => $ticketEmbedding->ticket,
                        'similarity' => $this->cosineSimilarity($queryEmbedding, $candidateEmbedding),
                    ];
                })
                ->sortByDesc('similarity')
                ->take(5)
                ->pluck('ticket')
                ->filter()
                ->values();
        } catch (Throwable $exception) {
            $this->logger->warning('Similar ticket lookup failed; returning no results.', [
                'ticket_id' => $ticket->getKey(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    private function supportsVectorQueries(): bool
    {
        return $this->ticketEmbedding->getConnection()->getDriverName() === 'pgsql';
    }

    /**
     * @param  SupportCollection<int, float>  $left
     * @param  SupportCollection<int, float>  $right
     */
    private function cosineSimilarity(SupportCollection $left, SupportCollection $right): float
    {
        $dot = $left->zip($right)->sum(fn ($pair): float => (float) ($pair[0] * $pair[1]));
        $leftMagnitude = sqrt($left->sum(fn (float $value): float => $value * $value));
        $rightMagnitude = sqrt($right->sum(fn (float $value): float => $value * $value));

        if ($leftMagnitude === 0.0 || $rightMagnitude === 0.0) {
            return 0.0;
        }

        return $dot / ($leftMagnitude * $rightMagnitude);
    }
}
