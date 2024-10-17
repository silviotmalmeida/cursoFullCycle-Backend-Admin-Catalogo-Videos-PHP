<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Entity;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface EntityRepositoryInterface
{
    public function insert(Entity $entity): Entity;

    public function findById(string $entityId): Entity;

    public function findByIdArray(array $listIds): array;

    public function findAll(?string $filter = '', string $order = 'ASC'): array;

    public function paginate(?string $filter = '', string $order = 'ASC', int $page = 1, int $perPage = 15): PaginationInterface;

    public function update(Entity $entity): Entity;

    public function deleteById(string $entityId): bool;
}
