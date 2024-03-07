<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Category as CategoryModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\Category as CategoryEntity;
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
    private function toCategory(CategoryModel $object): CategoryEntity
    {
        $category = new CategoryEntity(
            id: $object->id,
            name: $object->name,
            description: $object->description,
            createdAt: $object->created_at,
            updatedAt: $object->updated_at
        );

        ((bool) $object->is_active) ? $category->activate() : $category->deactivate();

        return $category;
    }

    // função de inserção no bd
    public function insert(CategoryEntity $category): CategoryEntity
    {
        // inserindo os dados recebidos
        $response = $this->model->create(
            [
                'id' => $category->id(),
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->isActive,
                'created_at' => $category->createdAt(),
                'updated_at' => $category->updatedAt(),
            ]
        );
        // retornando a entidade populada com os dados inseridos
        return $this->toCategory($response);
    }

    // função de busca por id
    public function findById(string $categoryId): CategoryEntity
    {
        // buscando no bd
        $categoryDb = $this->model->find($categoryId);
        // se não houver retorno, lança exceção
        if (!$categoryDb) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toCategory($categoryDb);
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
    public function findAll(string $filter = '', string $order = 'DESC'): array
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query->orderBy('id', $order);
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
        if ($filter) $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query->orderBy('id', $order);
        // executando a busca paginada
        $paginator = $query->paginate($perPage);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(CategoryEntity $category): CategoryEntity
    {
        // buscando no bd
        $categoryDb = $this->model->find($category->id());
        // se não houver retorno, lança exceção
        if (!$categoryDb) throw new NotFoundException('ID not found');
        // executando a atualização
        $categoryDb->update([
            'id' => $category->id(),
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => $category->isActive,
            'updated_at' => new DateTime()
        ]);
        // forçando a atualização do registro
        $categoryDb->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toCategory($categoryDb);
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
