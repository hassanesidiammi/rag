<?php

declare(strict_types=1);

namespace App\Llm;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LlmClientFactory
{
    public function __construct(
        private OpenAiClient $openAi,
        private MistralClient $mistral,
        #[Autowire(env: 'LLM_PROVIDER')]
        private string $provider,
    ) {
    }

    public function create(): LlmClientInterface
    {
        return match ($this->provider) {
            'openai' => $this->openAi,
            'mistral' => $this->mistral,
            default => throw new \InvalidArgumentException(
                sprintf('Unknown LLM provider "%s". Set LLM_PROVIDER to "openai" or "mistral".', $this->provider),
            ),
        };
    }
}
