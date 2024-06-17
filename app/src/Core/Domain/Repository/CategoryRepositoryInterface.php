<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Category;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface CategoryRepositoryInterface extends EntityRepositoryInterface
{
    public function findByIdArray(array $listIds): array;
}
