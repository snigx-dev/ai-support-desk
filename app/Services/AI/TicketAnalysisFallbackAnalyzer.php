<?php

namespace App\Services\AI;

use App\Data\Tickets\TicketAnalysisInputData;
use App\Data\Tickets\TicketAnalysisData;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;

class TicketAnalysisFallbackAnalyzer
{
    public function analyze(TicketAnalysisInputData $input): TicketAnalysisData
    {
        $text = strtolower($input->title.' '.$input->message);
        $category = TicketCategory::fromTicketText($text);

        return new TicketAnalysisData(
            summary: $this->summarize($input),
            priority: $this->resolvePriority($text),
            category: $category,
            categoryLabel: $category->label(),
            rawResponseJson: json_encode([
                'source' => 'fallback',
                'ticket_id' => $input->ticketId,
            ], JSON_THROW_ON_ERROR),
        );
    }

    private function summarize(TicketAnalysisInputData $input): string
    {
        return trim(sprintf(
            'Fallback analysis for "%s": %s',
            $input->title,
            mb_strimwidth($input->message, 0, 140, '...'),
        ));
    }

    private function resolvePriority(string $text): TicketPriority
    {
        if (str_contains($text, 'urgent') || str_contains($text, 'critical') || str_contains($text, 'outage') || str_contains($text, 'blocked')) {
            return TicketPriority::Urgent;
        }

        if (str_contains($text, 'down') || str_contains($text, 'cannot') || str_contains($text, 'unable') || str_contains($text, 'error')) {
            return TicketPriority::High;
        }

        if (str_contains($text, 'how') || str_contains($text, 'question') || str_contains($text, 'feature') || str_contains($text, 'request')) {
            return TicketPriority::Low;
        }

        return TicketPriority::Medium;
    }
}
