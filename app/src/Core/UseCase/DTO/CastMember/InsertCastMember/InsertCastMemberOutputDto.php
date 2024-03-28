<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\CastMember\InsertCastMember;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertCastMemberOutputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public string $name,
        public int $type,
        public string $created_at,
        public string $updated_at,
    ) {
    }
}
