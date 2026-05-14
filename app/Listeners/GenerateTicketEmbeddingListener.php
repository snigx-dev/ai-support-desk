<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Jobs\GenerateTicketEmbeddingJob;
use Illuminate\Contracts\Bus\Dispatcher;

class GenerateTicketEmbeddingListener
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function handle(TicketCreated $event): void
    {
        $this->dispatcher->dispatch(new GenerateTicketEmbeddingJob($event->ticket->getKey()));
    }
}
