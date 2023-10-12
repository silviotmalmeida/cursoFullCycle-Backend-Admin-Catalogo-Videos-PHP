<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Category;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface CategoryRepositoryInterface
{
    public function insert(Category $category): Category;

    public function findById(string $categoryId): Category;

    public function getIdsListIds(array $categoriesId = []): array;

    public function findAll(string $filter = '', string $order = 'DESC'): array;

    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $itemsForPage = 15): PaginationInterface;

    public function update(Category $category): Category;

    public function delete(string $categoryId): bool;
}
