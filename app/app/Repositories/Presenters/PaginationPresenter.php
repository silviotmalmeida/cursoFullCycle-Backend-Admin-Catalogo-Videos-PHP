<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Presenters;

// importações
use Core\Domain\Repository\PaginationInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use stdClass;

// definindo o presenter, que implementa a interface PaginationInterface
class PaginationPresenter implements PaginationInterface
{
    // atributo que irá armazenar o array de itens como objetos
    /**
     * @return stdClass[]
     */
    protected array $items = [];

    // construtor
    public function __construct(
        protected LengthAwarePaginator $paginator
    ) {
        // populando o array de itens como objetos
        $this->items = $this->resolveItems($this->paginator->items());
    }

    /**
     * @return stdClass[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->paginator->total();
    }

    public function lastPage(): int
    {
        return $this->paginator->lastPage();
    }

    public function firstPage(): int
    {
        return 1;
    }

    public function currentPage(): int
    {
        return $this->paginator->currentPage();
    }

    public function perPage(): int
    {
        return $this->paginator->perPage();
    }

    public function to(): int
    {
        return $this->paginator->firstItem() ?? 0;
    }

    public function from(): int
    {
        return $this->paginator->lastItem() ?? 0;
    }

    // função que vai converter a collection em um array de objetos
    private function resolveItems(array $items): array
    {
        // inicializando o array de saída vazio
        $response = [];

        // iterando a collection de entrada
        foreach ($items as $item) {
            // criando um objeto genérico
            $object = new stdClass;
            // iterando sobre os atributos do item da collection de entrada
            foreach ($item->toArray() as $key => $value) {
                // populando o objeto genérico com os atributos do item da collection de entrada
                $object->{$key} = $value;
            }
            // adicionando o objeto no array de saída
            array_push($response, $object);
        }

        return $response;
    }
}
