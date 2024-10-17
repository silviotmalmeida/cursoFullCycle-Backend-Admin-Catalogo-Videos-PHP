<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Genre as GenreModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\Entity;
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
    private function toGenre(GenreModel $model): Entity
    {
        $entity = new GenreEntity(
            id: $model->id,
            name: $model->name,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
        // atribuindo o is_active
        ((bool) $model->is_active) ? $entity->activate() : $entity->deactivate();
        // adicionando as categories
        if ($model->categories) {
            foreach ($model->categories as $category) {

                $entity->addCategoryId($category->id);
            }
        }
        
        return $entity;
    }

    // função auxiliar para sincronizar os relacionamentos
    private function syncRelationships(Entity $entity, GenreModel $model)
    {
        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($entity->categoriesId); $i++) {
            array_push($arraySync, strval($entity->categoriesId[$i]));
        }
        $model->categories()->sync($arraySync);
    }

    // função de inserção no bd
    public function insert(Entity $entity): GenreEntity
    {
        // inserindo os dados recebidos
        $model = $this->model->create(
            [
                'id' => $entity->id(),
                'name' => $entity->name,
                'is_active' => $entity->isActive,
                'created_at' => $entity->createdAt(),
                'updated_at' => $entity->updatedAt(),
            ]
        );

        // sincronizando os relacionamentos
        $this->syncRelationships($entity, $model);

        // retornando a entidade populada com os dados inseridos
        return $this->toGenre($model);
    }

    // função de busca por id
    public function findById(string $genreId): Entity
    {
        // buscando no bd
        $model = $this->model->find($genreId);
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toGenre($model);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $models = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($models as $model) {
            array_push($response, $this->toGenre($model));
        }
        // retornando a lista de entidades
        return $response;
    }

    // função de busca geral
    public function findAll(?string $filter = '', string $order = 'ASC'): array
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
    public function paginate(?string $filter = '', string $order = 'ASC', int $page = 1, int $perPage = 15): PaginationInterface
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query = $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('name', $order);
        // executando a busca paginada
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(Entity $entity): Entity
    {
        // buscando no bd
        $model = $this->model->find($entity->id());
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // executando a atualização
        $model->update([
            'id' => $entity->id(),
            'name' => $entity->name,
            'is_active' => $entity->isActive,
            'updated_at' => new DateTime()
        ]);

        // sincronizando os relacionamentos
        $this->syncRelationships($entity, $model);

        // forçando a atualização do registro
        $model->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toGenre($model);
    }

    // função de remoção
    public function deleteById(string $genreId): bool
    {
        // buscando no bd
        $model = $this->model->find($genreId);
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // removendo o registro
        $model->delete();
        return true;
    }
}
