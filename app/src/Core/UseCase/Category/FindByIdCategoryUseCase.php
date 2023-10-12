<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryInputDto;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryOutputDto;

// definindo o usecase
class FindByIdCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(FindByIdCategoryInputDto $input): FindByIdCategoryOutputDto
    {
        // buscando a entidade no BD utilizando o repository
        $category = $this->repository->findById($input->id);

        // retornando os dados
        return new FindByIdCategoryOutputDto(
            id: $category->id(),
            name: $category->name,
            description: $category->description,
            is_active: $category->isActive,
            created_at: $category->createdAt(),
            updated_at: $category->updatedAt(),
        );
    }
}
