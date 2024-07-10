<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Update\DTO;

// importações
use Core\Domain\Enum\Rating;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateVideoInputDto
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
        public array $categoriesId,
        public array $genresId,
        public array $castMembersId,
        public ?array $thumbFile = null,
        public ?array $thumbHalf = null,
        public ?array $bannerFile = null,
        public ?array $trailerFile = null,
        public ?array $videoFile = null,
    ) {
    }
}
