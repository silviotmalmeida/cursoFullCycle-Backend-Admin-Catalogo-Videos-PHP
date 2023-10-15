<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryInputDto;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryOutputDto;

// definindo o usecase
class UpdateCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(UpdateCategoryInputDto $input): UpdateCategoryOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $category = $this->repository->findById($input->id);

        // atualizando os dados da category
        $category->update($input->name, $input->description);

        // atualizando a entidade no BD utilizando o repository
        $updatedCategory = $this->repository->update($category);

        // retornando os dados
        return new UpdateCategoryOutputDto(
            id: $updatedCategory->id(),
            name: $updatedCategory->name,
            description: $updatedCategory->description,
            is_active: $updatedCategory->isActive,
            created_at: $updatedCategory->createdAt(),
            updated_at: $updatedCategory->updatedAt(),
        );
    }
}
