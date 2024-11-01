<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\UpdateEncodedVideoPath\DTO;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateEncodedVideoPathOutputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public ?string $encodedPath,        
    ) {
    }
}
