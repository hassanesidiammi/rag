<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AskRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 4000)]
        public string $question,
    ) {
    }
}
