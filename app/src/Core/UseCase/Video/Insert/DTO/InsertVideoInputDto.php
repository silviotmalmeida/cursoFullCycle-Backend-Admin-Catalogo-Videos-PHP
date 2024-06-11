<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert\DTO;

// importações
use Core\Domain\Enum\Rating;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;

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
        public ?Image $thumbFile = null,
        public ?Image $thumbHalf = null,
        public ?Image $bannerFile = null,
        public ?Media $trailerFile = null,
        public ?Media $videoFile = null,
        public array $castMembersId = [],
        public array $categoriesId = [],
        public array $genresId = [],
    ) {
    }
}
