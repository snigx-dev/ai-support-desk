<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'title', 'message', 'status', 'priority', 'ai_summary', 'ai_category', 'ai_raw_response', 'ai_reply', 'ai_reply_confidence'])]
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'ai_reply_confidence' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function embedding(): HasOne
    {
        return $this->hasOne(TicketEmbedding::class);
    }
}
