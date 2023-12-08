<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Category;

// importações
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryInputDto;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryOutputDto;

// definindo o usecase
class PaginateCategoryUseCase
{
    // construtor e atributos
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(PaginateCategoryInputDto $input): PaginateCategoryOutputDto
    {
        // buscando as entidades no BD utilizando o repository
        $pagination = $this->repository->paginate(
            filter: $input->filter,
            order: $input->order,
            startPage: $input->startPage,
            itemsForPage: $input->itemsForPage
        );

        // retornando os dados
        return new PaginateCategoryOutputDto(
            items: $pagination->items(),
            total: $pagination->total(),
            last_page: $pagination->lastPage(),
            first_page: $pagination->firstPage(),
            current_page: $pagination->currentPage(),
            per_page: $pagination->perPage(),
            to: $pagination->to(),
            from: $pagination->from(),
        );
    }
}
