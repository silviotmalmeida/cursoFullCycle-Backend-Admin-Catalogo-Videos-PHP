<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Genre as GenreModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\Genre as GenreEntity;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\Domain\Repository\PaginationInterface;
use DateTime;

// definindo o repository, que implementa a interface GenreRepositoryInterface
class GenreEloquentRepository implements GenreRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new GenreModel()
    ) {
    }

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toGenre(GenreModel $object): GenreEntity
    {
        $Genre = new GenreEntity(
            id: $object->id,
            name: $object->name,
            createdAt: $object->created_at,
            updatedAt: $object->updated_at
        );

        ((bool) $object->is_active) ? $Genre->activate() : $Genre->deactivate();

        return $Genre;
    }

    // função de inserção no bd
    public function insert(GenreEntity $Genre): GenreEntity
    {
        // inserindo os dados recebidos
        $response = $this->model->create(
            [
                'id' => $Genre->id(),
                'name' => $Genre->name,
                'is_active' => $Genre->isActive,
                'created_at' => $Genre->createdAt(),
                'updated_at' => $Genre->updatedAt(),
            ]
        );

        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($Genre->categoriesId); $i++) {
            array_push($arraySync, strval($Genre->categoriesId[$i]));
        }
        $response->categories()->sync($arraySync);

        // retornando a entidade populada com os dados inseridos
        return $this->toGenre($response);
    }

    // função de busca por id
    public function findById(string $GenreId): GenreEntity
    {
        // buscando no bd
        $GenreDb = $this->model->find($GenreId);
        // se não houver retorno, lança exceção
        if (!$GenreDb) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toGenre($GenreDb);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $categoriesDb = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($categoriesDb as $GenreDb) {
            array_push($response, $this->toGenre($GenreDb));
        }
        // retornando a lista de entidades
        return $response;
    }

    // função de busca geral
    public function findAll(string $filter = '', string $order = 'DESC'): array
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query = $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('name', $order);
        // executando a busca
        $response = $query->get();
        // retornando os dados
        return $response->toArray();
    }

    // função de busca paginada
    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $perPage = 15): PaginationInterface
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query = $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('id', $order);
        // executando a busca paginada
        $paginator = $query->paginate($perPage);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(GenreEntity $Genre): GenreEntity
    {
        // buscando no bd
        $GenreDb = $this->model->find($Genre->id());
        // se não houver retorno, lança exceção
        if (!$GenreDb) throw new NotFoundException('ID not found');
        // executando a atualização
        $GenreDb->update([
            'id' => $Genre->id(),
            'name' => $Genre->name,
            'is_active' => $Genre->isActive,
            'updated_at' => new DateTime()
        ]);

        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($Genre->categoriesId); $i++) {
            array_push($arraySync, strval($Genre->categoriesId[$i]));
        }
        $GenreDb->categories()->sync($arraySync);

        // forçando a atualização do registro
        $GenreDb->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toGenre($GenreDb);
    }

    // função de remoção
    public function deleteById(string $GenreId): bool
    {
        // buscando no bd
        $GenreDb = $this->model->find($GenreId);
        // se não houver retorno, lança exceção
        if (!$GenreDb) throw new NotFoundException('ID not found');
        // removendo o registro
        $GenreDb->delete();
        return true;
    }
}
