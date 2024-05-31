<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert\DTO;

// importações
use Core\Domain\Enum\Rating;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertVideoOutputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public int $yearLaunched,
        public int $duration,
        public bool $opened,
        public Rating $rating,
        public string $created_at,
        public string $updated_at,
    ) {
    }
}
