<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\CastMember\UpdateCastMember;

use Core\Domain\Enum\CastMemberType;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateCastMemberInputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public ?string $name,
        public null|int|CastMemberType $type,
    ) {
    }
}
