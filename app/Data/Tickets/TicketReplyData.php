<?php

namespace App\Data\Tickets;

use App\Exceptions\TicketReplyException;
use Laravel\Ai\Responses\StructuredAgentResponse;

final readonly class TicketReplyData
{
    public function __construct(
        public string $reply,
        public float $confidence,
        public string $usedContextSummary,
    ) {
    }

    public static function fromStructuredResponse(StructuredAgentResponse $response): self
    {
        $reply = self::stringValue($response['reply'] ?? null, 'reply');
        $confidence = self::floatValue($response['confidence'] ?? null, 'confidence');
        $usedContextSummary = self::stringValue($response['used_context_summary'] ?? null, 'used_context_summary');

        if ($confidence < 0.0 || $confidence > 1.0) {
            throw TicketReplyException::invalidResponse('AI confidence must be between 0.0 and 1.0.');
        }

        return new self(
            reply: $reply,
            confidence: $confidence,
            usedContextSummary: $usedContextSummary,
        );
    }

    public static function fallback(string $usedContextSummary): self
    {
        return new self(
            reply: 'We are reviewing your request and will respond shortly.',
            confidence: 0.1,
            usedContextSummary: $usedContextSummary,
        );
    }

    /**
     * @throws TicketReplyException
     */
    private static function stringValue(mixed $value, string $key): string
    {
        if (! is_string($value)) {
            throw TicketReplyException::invalidResponse(sprintf('AI field "%s" must be a string.', $key));
        }

        $value = trim($value);

        if ($value === '') {
            throw TicketReplyException::invalidResponse(sprintf('AI field "%s" may not be empty.', $key));
        }

        return $value;
    }

    /**
     * @throws TicketReplyException
     */
    private static function floatValue(mixed $value, string $key): float
    {
        if (! is_int($value) && ! is_float($value) && ! (is_string($value) && is_numeric($value))) {
            throw TicketReplyException::invalidResponse(sprintf('AI field "%s" must be numeric.', $key));
        }

        return (float) $value;
    }
}
