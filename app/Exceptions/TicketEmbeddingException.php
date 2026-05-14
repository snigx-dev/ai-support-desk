<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class TicketEmbeddingException extends RuntimeException
{
    public static function generationFailed(
        string $message,
        ?Throwable $previous = null,
    ): self {
        return new self($message, previous: $previous);
    }
}
