<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\AskRequest;
use App\Llm\LlmClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AskController extends AbstractController
{
    #[Route('/ask', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] AskRequest $payload,
        LlmClientInterface $llm,
    ): JsonResponse {
        return new JsonResponse([
            'answer' => $llm->complete($payload->question),
        ]);
    }
}
