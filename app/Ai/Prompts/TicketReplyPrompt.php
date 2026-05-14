<?php

namespace App\AI\Prompts;

use App\Data\Tickets\TicketReplyContextTicketData;
use App\Models\Ticket;
use Illuminate\Support\Collection;

final readonly class TicketReplyPrompt
{
    /**
     * @param  Collection<int, TicketReplyContextTicketData>  $similarTickets
     */
    public function __construct(
        public string $title,
        public string $message,
        public Collection $similarTickets,
    ) {
    }

    /**
     * @param  Collection<int, Ticket>  $similarTickets
     */
    public static function forTicket(Ticket $ticket, Collection $similarTickets): self
    {
        return new self(
            title: $ticket->title,
            message: $ticket->message,
            similarTickets: $similarTickets
                ->map(static fn (Ticket $similarTicket): TicketReplyContextTicketData => TicketReplyContextTicketData::fromTicket($similarTicket))
                ->values(),
        );
    }

    public function toPrompt(): string
    {
        return <<<PROMPT
Write a helpful support reply using only the provided context.
Do not invent facts, policies, or resolutions.
Prefer the patterns and resolutions from similar tickets.
If the context is insufficient, keep the reply general and set low confidence.
Return only structured output.

Current ticket:
Title: {$this->title}
Message: {$this->message}

Similar tickets:
{$this->similarTicketsBlock()}
PROMPT;
    }

    private function similarTicketsBlock(): string
    {
        if ($this->similarTickets->isEmpty()) {
            return 'None';
        }

        return $this->similarTickets
            ->map(static fn (TicketReplyContextTicketData $ticket): string => $ticket->toPromptBlock())
            ->implode("\n\n");
    }
}
