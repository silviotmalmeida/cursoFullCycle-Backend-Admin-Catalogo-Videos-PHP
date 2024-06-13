<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert\DTO;

// importações
use Core\Domain\Enum\Rating;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;

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
        public ?Image $thumbFile,
        public ?Image $thumbHalf,
        public ?Image $bannerFile,
        public ?Media $trailerFile,
        public ?Media $videoFile,
        public array $categoriesId,
        public array $genresId,
        public array $castMembersId,
        public string $created_at,
        public string $updated_at,
    ) {
    }
}
