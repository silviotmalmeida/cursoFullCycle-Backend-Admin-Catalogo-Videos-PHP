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

// definindo o repository, que implementa a interface CategoryRepositoryInterface
class CategoryEloquentRepository implements CategoryRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new CategoryModel()
    ) {
    }

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toCategory(object $object): CategoryEntity
    {
        return new CategoryEntity(
            id: $object->id,
            name: $object->name,
            description: $object->description,
            isActive: $object->is_active,
            createdAt: $object->created_at,
            updatedAt: $object->updated_at
        );
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
        $response = $this->model->find($categoryId);
        // se não houver retorno, lança exceção
        if (!$response) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toCategory($response);
    }

    public function getIdsListIds(array $categoriesId = []): array
    {
        return [];
    }

    // função de busca por id
    public function findAll(string $filter = '', string $order = 'DESC'): array
    {
        // buscando no bd
        $response = $this->model
            ->where(function ($query) use ($filter) {
                if ($filter) $query->where('name', 'LIKE', "%{$filter}%");
            })
            ->orderBy('id', $order)
            ->get();


        return $response->toArray();
    }

    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $itemsForPage = 15): PaginationInterface
    {
        return new PaginationPresenter();
    }

    public function update(CategoryEntity $category): CategoryEntity
    {
        return new CategoryEntity();
    }

    public function delete(string $categoryId): bool
    {
        return true;
    }
}
