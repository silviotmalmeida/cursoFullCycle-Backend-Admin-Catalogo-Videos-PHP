<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\InsertCategory\InsertCategoryInputDto;
use Core\UseCase\DTO\Category\InsertCategory\InsertCategoryOutputDto;

// definindo o usecase
class InsertCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(InsertCategoryInputDto $input): InsertCategoryOutputDto
    {
        // criando a entidade com os dados do input
        $category = new Category(
            name: $input->name,
            description: $input->description,
            isActive: $input->isActive,
        );

        // inserindo a entidade no BD utilizando o repository
        $newCategory = $this->repository->insert($category);

        // retornando os dados
        return new InsertCategoryOutputDto(
            id: $newCategory->id(),
            name: $newCategory->name,
            description: $newCategory->description,
            is_active: $newCategory->isActive,
            created_at: $newCategory->createdAt(),
            updated_at: $newCategory->updatedAt(),
        );
    }
}
