<?php

namespace App\Jobs;

use App\Data\Tickets\TicketAnalysisInputData;
use App\Events\TicketAnalyzed;
use App\Models\Ticket;
use App\Services\AI\TicketAnalyzer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzeTicketJob implements ShouldQueue, ShouldBeUnique
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
        return 'ticket-analysis:'.$this->ticketId;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function handle(TicketAnalyzer $analyzer): void
    {
        $ticket = Ticket::query()->find($this->ticketId);

        if ($ticket === null) {
            return;
        }

        $analysis = $analyzer->analyze(TicketAnalysisInputData::fromTicket($ticket));

        $ticket->forceFill([
            'ai_summary' => $analysis->summary,
            'priority' => $analysis->priority,
            'ai_category' => $analysis->categoryLabel,
            'ai_raw_response' => $analysis->rawResponseJson,
        ])->saveQuietly();

        TicketAnalyzed::dispatch($ticket);
    }

    public function failed(Throwable $exception): void
    {
        logger()->error('Ticket analysis job failed.', [
            'ticket_id' => $this->ticketId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
