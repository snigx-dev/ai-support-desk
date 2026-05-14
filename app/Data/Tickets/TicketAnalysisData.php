<?php

namespace App\Data\Tickets;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Exceptions\TicketAnalysisException;
use JsonException;
use Laravel\Ai\Responses\StructuredAgentResponse;

final readonly class TicketAnalysisData
{
    public function __construct(
        public string $summary,
        public TicketPriority $priority,
        public TicketCategory $category,
        public string $categoryLabel,
        public string $rawResponseJson,
    ) {
    }

    public static function fromStructuredResponse(StructuredAgentResponse $response): self
    {
        $summary = self::stringValue($response['summary'] ?? null, 'summary');
        $priorityValue = strtolower(self::stringValue($response['priority'] ?? null, 'priority'));
        $categoryLabel = self::stringValue($response['category'] ?? null, 'category');

        $priority = TicketPriority::tryFrom($priorityValue);

        if ($priority === null) {
            throw TicketAnalysisException::invalidResponse('AI priority is not supported.');
        }

        $category = TicketCategory::fromLabel($categoryLabel);

        return new self(
            summary: $summary,
            priority: $priority,
            category: $category,
            categoryLabel: $category === TicketCategory::Other ? $categoryLabel : $category->label(),
            rawResponseJson: self::encodeResponse($response->toArray()),
        );
    }

    /**
     * @throws JsonException
     */
    public function withFallbackSource(string $source): self
    {
        return new self(
            summary: $this->summary,
            priority: $this->priority,
            category: $this->category,
            categoryLabel: $this->categoryLabel,
            rawResponseJson: json_encode([
                'source' => $source,
                'summary' => $this->summary,
                'priority' => $this->priority->value,
                'category' => $this->categoryLabel,
            ], JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws TicketAnalysisException
     */
    private static function stringValue(mixed $value, string $key): string
    {
        if (! is_string($value)) {
            throw TicketAnalysisException::invalidResponse(sprintf('AI field "%s" must be a string.', $key));
        }

        $value = trim($value);

        if ($value === '') {
            throw TicketAnalysisException::invalidResponse(sprintf('AI field "%s" may not be empty.', $key));
        }

        return $value;
    }

    /**
     * @throws TicketAnalysisException
     */
    private static function encodeResponse(array $response): string
    {
        try {
            return json_encode($response, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw TicketAnalysisException::invalidResponse('AI response could not be encoded.', $exception);
        }
    }
}
