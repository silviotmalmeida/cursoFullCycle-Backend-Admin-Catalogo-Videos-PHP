<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\DeleteById\DTO;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class DeleteByIdVideoOutputDto
{
    // construtor e atributos
    public function __construct(
        public bool $sucess,
    ) {
    }
}
