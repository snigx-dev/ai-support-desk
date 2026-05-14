<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAnalyzed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
    ) {
    }
}
