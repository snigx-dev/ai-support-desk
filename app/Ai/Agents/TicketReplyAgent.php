<?php

namespace App\AI\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Temperature(0)]
#[MaxTokens(512)]
final class TicketReplyAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a support reply generator. Use only the provided ticket context and similar ticket history. Never invent missing facts.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'reply' => $schema->string()->required(),
            'confidence' => $schema->number()->min(0)->max(1)->required(),
            'used_context_summary' => $schema->string()->required(),
        ];
    }
}
