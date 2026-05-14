<?php

namespace App\Models;

use Database\Factories\TicketEmbeddingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'content', 'content_hash', 'embedding'])]
class TicketEmbedding extends Model
{
    /** @use HasFactory<TicketEmbeddingFactory> */
    use HasFactory;

    protected $casts = [
        'embedding' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
