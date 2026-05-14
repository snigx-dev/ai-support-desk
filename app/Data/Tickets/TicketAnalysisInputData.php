<?php

namespace App\Data\Tickets;

use App\Models\Ticket;

final readonly class TicketAnalysisInputData
{
    public function __construct(
        public int $ticketId,
        public string $title,
        public string $message,
    ) {
    }

    public static function fromTicket(Ticket $ticket): self
    {
        return new self(
            ticketId: $ticket->getKey(),
            title: $ticket->title,
            message: $ticket->message,
        );
    }

    public function toPrompt(): string
    {
        return <<<PROMPT
Analyze this support ticket and return structured output that matches the schema exactly.
Summary: 1 or 2 concise sentences.
Priority: low, medium, high, or urgent.
Category: short lowercase label describing the issue domain, such as access, account, billing, bug, feature request, performance, or general.
Ticket ID: {$this->ticketId}
Title: {$this->title}
Message: {$this->message}
PROMPT;
    }

    /**
     * @return array{ticket_id: int, title: string}
     */
    public function toLogContext(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'title' => $this->title,
        ];
    }
}
