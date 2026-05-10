<?php

declare(strict_types=1);

namespace App\Llm;

interface LlmClientInterface
{
    public function complete(string $prompt): string;
}
