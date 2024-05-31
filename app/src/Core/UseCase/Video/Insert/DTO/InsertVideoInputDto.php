<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert\DTO;

// importações
use Core\Domain\Enum\Rating;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertVideoInputDto
{
    // construtor e atributos
    public function __construct(
        public string $title,
        public string $description,
        public int $yearLaunched,
        public int $duration,
        public bool $opened,
        public Rating $rating,
        public array $categoriesId = [],
        public array $genresId = [],
        public array $castMembersId = [],
    ) {
    }
}
