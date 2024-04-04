<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\CastMember;

// importações
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\DTO\CastMember\PaginateCastMember\PaginateCastMemberInputDto;
use Core\UseCase\DTO\CastMember\PaginateCastMember\PaginateCastMemberOutputDto;

// definindo o usecase
class PaginateCastMemberUseCase
{
    // construtor e atributos
    public function __construct(
        protected CastMemberRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(PaginateCastMemberInputDto $input): PaginateCastMemberOutputDto
    {
        // buscando as entidades no BD utilizando o repository
        $pagination = $this->repository->paginate(
            filter: $input->filter,
            order: $input->order,
            page: $input->page,
            perPage: $input->perPage
        );

        // retornando os dados
        return new PaginateCastMemberOutputDto(
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
