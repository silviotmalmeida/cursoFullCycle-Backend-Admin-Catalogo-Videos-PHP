<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\CastMember\InsertCastMember;

use Core\Domain\Enum\CastMemberType;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertCastMemberInputDto
{
    // construtor e atributos
    public function __construct(
        public string $name,
        public CastMemberType|int $type,
    ) {
    }
}
