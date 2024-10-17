<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Video;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface VideoRepositoryInterface
{
    public function insert(Video $entity): Video;

    public function findById(string $entityId): Video;

    public function findByIdArray(array $listIds): array;

    public function findAll(?string $filter = '', string $order = 'ASC'): array;

    public function paginate(?string $filter = '', string $order = 'ASC', int $page = 1, int $perPage = 15): PaginationInterface;

    public function update(Video $entity): Video;

    public function deleteById(string $entityId): bool;

    public function updateMedia(Video $entity): Video;
}
