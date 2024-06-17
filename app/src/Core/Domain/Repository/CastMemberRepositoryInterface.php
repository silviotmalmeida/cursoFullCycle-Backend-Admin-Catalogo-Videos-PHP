<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\CastMember;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface CastMemberRepositoryInterface extends EntityRepositoryInterface
{
    public function findByIdArray(array $listIds): array;
}
