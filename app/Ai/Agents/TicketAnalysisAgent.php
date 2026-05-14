<?php

namespace App\Ai\Agents;

use App\Enums\TicketPriority;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Temperature(0)]
#[MaxTokens(256)]
final class TicketAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Analyze support tickets and return only structured output that matches the schema exactly.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required(),
            'priority' => $schema->string()
                ->enum(TicketPriority::values())
                ->required(),
            'category' => $schema->string()->required(),
        ];
    }
}
