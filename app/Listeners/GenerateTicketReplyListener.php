<?php

namespace App\Listeners;

use App\Events\TicketAnalyzed;
use App\Jobs\GenerateTicketReplyJob;
use Illuminate\Contracts\Bus\Dispatcher;

class GenerateTicketReplyListener
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function handle(TicketAnalyzed $event): void
    {
        $this->dispatcher->dispatch(new GenerateTicketReplyJob($event->ticket->getKey()));
    }
}
