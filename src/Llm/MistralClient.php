<?php

declare(strict_types=1);

namespace App\Llm;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class MistralClient implements LlmClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'MISTRAL_API_KEY')]
        private string $apiKey,
        #[Autowire(env: 'MISTRAL_MODEL')]
        private string $model,
    ) {
    }

    public function complete(string $prompt): string
    {
        $response = $this->httpClient->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
            'auth_bearer' => $this->apiKey,
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ],
            'timeout' => 30,
        ]);

        $data = $response->toArray();

        return $data['choices'][0]['message']['content'] ?? '';
    }
}
