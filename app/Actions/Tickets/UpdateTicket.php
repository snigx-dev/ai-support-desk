<?php

namespace App\Actions\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;

class UpdateTicket
{
    /**
     * @param array{
     *     title: string,
     *     message: string,
     *     status: TicketStatus|string,
     *     priority?: string|null
     * } $data
     */
    public function handle(Ticket $ticket, array $data): Ticket
    {
        $ticket->update([
            'title' => $data['title'],
            'message' => $data['message'],
            'status' => $data['status'],
            'priority' => $data['priority'] ?? null,
        ]);

        return $ticket->refresh();
    }
}
