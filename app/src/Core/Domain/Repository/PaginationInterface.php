<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// definindo a interface de retorno do método paginate() do repository
// contém a descrição dos métodos a serem implementados no retorno
interface PaginationInterface
{
    /**
     * @return stdClass[]
     */
    public function items(): array;

    public function total(): int;

    public function lastPage(): int;

    public function firstPage(): int;

    public function currentPage(): int;

    public function perPage(): int;

    public function to(): int;

    public function from(): int;
}
