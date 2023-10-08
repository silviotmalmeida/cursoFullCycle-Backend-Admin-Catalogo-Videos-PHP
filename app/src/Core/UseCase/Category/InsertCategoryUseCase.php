<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\CreateCategory\CategoryCreateInputDto;
use Core\UseCase\DTO\Category\CreateCategory\CategoryCreateOutputDto;

// definindo o usecase
class InsertCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    public function execute(CategoryCreateInputDto $input): CategoryCreateOutputDto
    {
        $category = new Category(
            name: $input->name,
            description: $input->description,
            isActive: $input->isActive,
        );

        $newCategory = $this->repository->insert($category);

        return new CategoryCreateOutputDto(
            id: $newCategory->id(),
            name: $newCategory->name,
            description: $category->description,
            is_active: $category->isActive,
            created_at: $category->createdAt(),
        );
    }
}
