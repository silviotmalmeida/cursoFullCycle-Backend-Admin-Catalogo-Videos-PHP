<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Genre;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface GenreRepositoryInterface
{
    public function insert(Genre $genre): Genre;

    public function findById(string $genreId): Genre;

    public function findByIdArray(array $listIds): array;

    public function findAll(string $filter = '', string $order = 'DESC'): array;

    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $perPage = 15): PaginationInterface;

    public function update(Genre $genre): Genre;

    public function deleteById(string $genreId): bool;
}
