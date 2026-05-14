<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\AI\EmbeddingGenerator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateTicketEmbeddingJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 1;

    public int $uniqueFor = 3600;

    public function __construct(public int $ticketId)
    {
    }

    public function uniqueId(): string
    {
        return 'ticket-embedding:'.$this->ticketId;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function handle(EmbeddingGenerator $generator): void
    {
        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $generator->generate($ticket);
    }

    public function failed(Throwable $exception): void
    {
        logger()->error('Ticket embedding job failed.', [
            'ticket_id' => $this->ticketId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
