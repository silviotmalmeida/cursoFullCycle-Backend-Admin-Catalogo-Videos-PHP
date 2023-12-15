<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Genre\FindByIdGenre;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class FindByIdGenreInputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
    ) {
    }
}
