<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Genre\InsertGenre;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertGenreInputDto
{
    // construtor e atributos
    public function __construct(
        public string $name,
        public bool $isActive = true,
        public array $categoriesId = [],
    ) {
    }
}
