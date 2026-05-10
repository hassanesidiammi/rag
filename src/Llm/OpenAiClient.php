<?php

declare(strict_types=1);

namespace App\Llm;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class OpenAiClient implements LlmClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'OPENAI_API_KEY')]
        private string $apiKey,
        #[Autowire(env: 'OPENAI_MODEL')]
        private string $model,
    ) {
    }

    public function complete(string $prompt): string
    {
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
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
