<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Update\DTO;

// importações
use Core\Domain\Enum\Rating;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateVideoOutputDto
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
        public array $categoriesId = [],
        public array $genresId = [],
        public array $castMembersId = [],
        public ?string $thumbFile = null,
        public ?string $thumbHalf = null,
        public ?string $bannerFile = null,
        public ?string $trailerFile = null,
        public ?string $videoFile = null,
    ) {
    }
}
