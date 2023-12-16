<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Genre\UpdateGenre;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateGenreInputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public ?string $name,
        public ?bool $isActive,
        public ?array $categoriesId,
    ) {
    }
}
