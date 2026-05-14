<?php

namespace App\Actions\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Events\TicketCreated;

class CreateTicket
{
    /**
     * @param array{
     *     title: string,
     *     message: string,
     *     status?: TicketStatus|string,
     *     priority?: string|null
     * } $data
     */
    public function handle(
        User $user,
        array $data,
    ): Ticket {
        $ticket = $user->tickets()->create([
            'title' => $data['title'],
            'message' => $data['message'],
            'status' => $data['status'] ?? TicketStatus::Open,
            'priority' => $data['priority'] ?? null,
        ]);

        TicketCreated::dispatch($ticket);

        return $ticket;
    }
}
