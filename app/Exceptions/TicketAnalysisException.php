<?php

namespace App\Exceptions;

use App\Data\Tickets\TicketAnalysisInputData;
use RuntimeException;
use Throwable;

class TicketAnalysisException extends RuntimeException
{
    public static function invalidResponse(
        string $message,
        ?Throwable $previous = null,
    ): self {
        return new self($message, previous: $previous);
    }

    public static function forTicket(
        TicketAnalysisInputData $input,
        Throwable $previous,
    ): self {
        return new self(
            message: 'Unable to analyze ticket '.$input->ticketId.'.',
            previous: $previous,
        );
    }
}
