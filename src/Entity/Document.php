<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Pgvector\Vector;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'documents')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public string $source;

    #[ORM\Column(name: 'chunk_index', type: Types::INTEGER)]
    public int $chunkIndex;

    #[ORM\Column(type: Types::TEXT)]
    public string $content;

    // Dimension is 1024 to match Mistral's mistral-embed model.
    // Switching providers means a schema change.
    #[ORM\Column(type: 'vector', length: 1024)]
    public Vector $embedding;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
