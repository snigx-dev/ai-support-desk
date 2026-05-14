<?php

namespace App\Providers;

use App\Events\TicketCreated;
use App\Events\TicketAnalyzed;
use App\Listeners\AnalyzeTicketListener;
use App\Listeners\GenerateTicketEmbeddingListener;
use App\Listeners\GenerateTicketReplyListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TicketCreated::class => [
            AnalyzeTicketListener::class,
            GenerateTicketEmbeddingListener::class,
        ],
        TicketAnalyzed::class => [
            GenerateTicketReplyListener::class,
        ],
    ];
}
