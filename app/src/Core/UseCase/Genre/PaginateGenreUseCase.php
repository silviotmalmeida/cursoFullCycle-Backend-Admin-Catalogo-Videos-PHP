<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\PaginateGenre\PaginateGenreInputDto;
use Core\UseCase\DTO\Genre\PaginateGenre\PaginateGenreOutputDto;

// definindo o usecase
class PaginateGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(PaginateGenreInputDto $input): PaginateGenreOutputDto
    {
        // buscando as entidades no BD utilizando o repository
        $pagination = $this->repository->paginate(
            filter: $input->filter,
            order: $input->order,
            page: $input->page,
            perPage: $input->perPage
        );

        // retornando os dados
        return new PaginateGenreOutputDto(
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
