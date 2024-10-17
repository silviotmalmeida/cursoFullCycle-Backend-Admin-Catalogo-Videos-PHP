<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Category as CategoryModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\Category as CategoryEntity;
use Core\Domain\Entity\Entity;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\PaginationInterface;
use DateTime;

// definindo o repository, que implementa a interface CategoryRepositoryInterface
class CategoryEloquentRepository implements CategoryRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new CategoryModel()
    ) {
    }

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toCategory(CategoryModel $model): Entity
    {
        $entity = new CategoryEntity(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );

        ((bool) $model->is_active) ? $entity->activate() : $entity->deactivate();

        return $entity;
    }

    // função de inserção no bd
    public function insert(Entity $entity): Entity
    {
        // inserindo os dados recebidos
        $model = $this->model->create(
            [
                'id' => $entity->id(),
                'name' => $entity->name,
                'description' => $entity->description,
                'is_active' => $entity->isActive,
                'created_at' => $entity->createdAt(),
                'updated_at' => $entity->updatedAt(),
            ]
        );
        // retornando a entidade populada com os dados inseridos
        return $this->toCategory($model);
    }

    // função de busca por id
    public function findById(string $categoryId): Entity
    {
        // buscando no bd
        $model = $this->model->find($categoryId);
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toCategory($model);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $categoriesDb = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($categoriesDb as $categoryDb) {
            array_push($response, $this->toCategory($categoryDb));
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
            'description' => $entity->description,
            'is_active' => $entity->isActive,
            'updated_at' => new DateTime()
        ]);
        // forçando a atualização do registro
        $model->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toCategory($model);
    }

    // função de remoção
    public function deleteById(string $categoryId): bool
    {
        // buscando no bd
        $categoryDb = $this->model->find($categoryId);
        // se não houver retorno, lança exceção
        if (!$categoryDb) throw new NotFoundException('ID not found');
        // removendo o registro
        $categoryDb->delete();
        return true;
    }
}
