<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert\DTO;

// importações
use Core\Domain\Enum\Rating;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertVideoInputDto
{
    // atributos fora do construtor
    public ?string $id;

    // construtor e atributos
    public function __construct(
        public string $title,
        public string $description,
        public int $yearLaunched,
        public int $duration,
        public bool $opened,
        public Rating|string $rating,
        public array $categoriesId = [],
        public array $genresId = [],
        public array $castMembersId = [],
        public ?array $thumbFile = null,
        public ?array $thumbHalf = null,
        public ?array $bannerFile = null,
        public ?array $trailerFile = null,
        public ?array $videoFile = null,
    ) {
        // setando o id nulo
        $this->id = null;
    }
}
