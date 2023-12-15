<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Genre\DeleteByIdGenre;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class DeleteByIdGenreInputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
    ) {
    }
}
