<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class TicketReplyException extends RuntimeException
{
    public static function invalidResponse(
        string $message,
        ?Throwable $previous = null,
    ): self {
        return new self($message, previous: $previous);
    }

    public static function forTicket(
        int $ticketId,
        Throwable $previous,
    ): self {
        return new self(
            message: 'Unable to generate reply for ticket '.$ticketId.'.',
            previous: $previous,
        );
    }
}
