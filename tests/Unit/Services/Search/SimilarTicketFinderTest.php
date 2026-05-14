<?php

namespace Tests\Unit\Services\Search;

use App\Models\Ticket;
use App\Models\TicketEmbedding;
use App\Services\AI\EmbeddingGenerator;
use App\Services\Search\SimilarTicketFinder;
use Illuminate\Support\Collection;
use Laravel\Ai\Embeddings;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class SimilarTicketFinderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_returns_the_top_five_similar_tickets_using_vector_similarity_fallback(): void
    {
        Embeddings::fake(function () {
            return [[1.0, 0.0]];
        })->preventStrayEmbeddings();

        $ticket = (new Ticket)->forceFill([
            'id' => 100,
            'title' => 'Portal outage',
            'message' => 'The portal is returning a 500 error after login.',
        ]);
        $ticket->exists = true;

        $tickets = collect([
            $this->makeEmbeddingResult(1, [1.0, 0.0], 'First', 'Best match'),
            $this->makeEmbeddingResult(2, [0.9, 0.1], 'Second', 'Second best'),
            $this->makeEmbeddingResult(3, [0.4, 0.6], 'Third', 'Lower match'),
            $this->makeEmbeddingResult(4, [0.3, 0.7], 'Fourth', 'Lower match'),
            $this->makeEmbeddingResult(5, [0.2, 0.8], 'Fifth', 'Lower match'),
            $this->makeEmbeddingResult(6, [0.1, 0.9], 'Sixth', 'Lowest match'),
        ]);

        $connection = Mockery::mock();
        $connection->shouldReceive('getDriverName')->andReturn('sqlite');

        $ticketEmbeddingModel = Mockery::mock(TicketEmbedding::class)->makePartial();
        $ticketEmbeddingModel->shouldReceive('getConnection')->andReturn($connection);
        $builder = Mockery::mock();
        $builder->shouldReceive('with')->with('ticket')->andReturnSelf();
        $builder->shouldReceive('where')->with('ticket_id', '!=', 100)->andReturnSelf();
        $builder->shouldReceive('get')->andReturn($tickets);
        $ticketEmbeddingModel->shouldReceive('newQuery')->andReturn($builder);

        $generator = new EmbeddingGenerator(new TicketEmbedding, Mockery::mock(LoggerInterface::class));
        $finder = new SimilarTicketFinder($ticketEmbeddingModel, $generator, Mockery::mock(LoggerInterface::class));

        $results = $finder->findSimilar($ticket);

        $this->assertCount(5, $results);
        $this->assertSame(1, $results->first()->getKey());
        $this->assertSame(5, $results->last()->getKey());
    }

    private function makeEmbeddingResult(
        int $ticketId,
        array $embedding,
        string $title,
        string $summary,
    ): TicketEmbedding {
        $ticket = (new Ticket)->forceFill([
            'id' => $ticketId,
            'title' => $title,
            'ai_summary' => $summary,
            'ai_reply' => $summary,
        ]);
        $ticket->exists = true;

        $embeddingModel = (new TicketEmbedding)->forceFill([
            'ticket_id' => $ticketId,
            'embedding' => $embedding,
        ]);
        $embeddingModel->setRelation('ticket', $ticket);

        return $embeddingModel;
    }
}
