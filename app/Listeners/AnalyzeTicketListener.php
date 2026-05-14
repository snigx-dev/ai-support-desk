<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Jobs\AnalyzeTicketJob;
use Illuminate\Contracts\Bus\Dispatcher;

class AnalyzeTicketListener
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function handle(TicketCreated $event): void
    {
        $this->dispatcher->dispatch(new AnalyzeTicketJob($event->ticket->getKey()));
    }
}
