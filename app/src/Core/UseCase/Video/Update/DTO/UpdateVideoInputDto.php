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
        public ?string $title = null,
        public ?string $description = null,
        public ?int $yearLaunched = null,
        public ?int $duration = null,
        public ?bool $opened = null,
        public Rating|string|null $rating = null,
        public ?array $categoriesId = null,
        public ?array $genresId = null,
        public ?array $castMembersId = null,
        public ?array $thumbFile = null,
        public ?array $thumbHalf = null,
        public ?array $bannerFile = null,
        public ?array $trailerFile = null,
        public ?array $videoFile = null,
    ) {
    }
}
