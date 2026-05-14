<?php

namespace App\Data\Tickets;

use App\Models\Ticket;

final readonly class TicketReplyContextTicketData
{
    public function __construct(
        public int $ticketId,
        public string $title,
        public string $message,
        public ?string $summary,
        public ?string $reply,
        public ?string $status,
    ) {
    }

    public static function fromTicket(Ticket $ticket): self
    {
        return new self(
            ticketId: $ticket->getKey(),
            title: $ticket->title,
            message: $ticket->message,
            summary: $ticket->ai_summary,
            reply: $ticket->ai_reply,
            status: $ticket->status?->value,
        );
    }

    public function toPromptBlock(): string
    {
        $lines = [
            sprintf('Ticket #%d', $this->ticketId),
            'Title: '.$this->title,
            'Message: '.$this->message,
        ];

        if ($this->status !== null) {
            $lines[] = 'Status: '.$this->status;
        }

        if ($this->summary !== null) {
            $lines[] = 'Summary: '.$this->summary;
        }

        if ($this->reply !== null) {
            $lines[] = 'Resolution: '.$this->reply;
        }

        return implode("\n", $lines);
    }

}
