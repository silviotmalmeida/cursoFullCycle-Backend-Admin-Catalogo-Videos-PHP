<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\DeleteCategory\DeleteCategoryInputDto;
use Core\UseCase\DTO\Category\DeleteCategory\DeleteCategoryOutputDto;

// definindo o usecase
class DeleteCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(DeleteCategoryInputDto $input): DeleteCategoryOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $category = $this->repository->findById($input->id);

        // deletando a entidade no BD utilizando o repository
        $sucess = false;
        if ($category->id()) $sucess = $this->repository->delete($input->id);

        // retornando os dados
        return new DeleteCategoryOutputDto(
            sucess: $sucess,
        );
    }
}
