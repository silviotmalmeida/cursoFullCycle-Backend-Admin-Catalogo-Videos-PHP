<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\CastMember;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface CastMemberRepositoryInterface
{
    public function insert(CastMember $castMember): CastMember;

    public function findById(string $castMemberId): CastMember;

    public function findAll(string $filter = '', string $order = 'DESC'): array;

    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $perPage = 15): PaginationInterface;

    public function update(CastMember $castMember): CastMember;

    public function deleteById(string $castMemberId): bool;
}
