<?php

namespace Tests;

use Laravel\Ai\Embeddings;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
    protected function setUp(): void
    {
        parent::setUp();

        Embeddings::fake(function ($prompt): array {
            return array_map(
                static fn (): array => Embeddings::fakeEmbedding($prompt->dimensions),
                $prompt->inputs,
            );
        })->preventStrayEmbeddings();
    }
}
