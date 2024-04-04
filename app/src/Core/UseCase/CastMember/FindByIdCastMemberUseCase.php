<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\CastMember;

// importações
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\DTO\CastMember\FindByIdCastMember\FindByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\FindByIdCastMember\FindByIdCastMemberOutputDto;

// definindo o usecase
class FindByIdCastMemberUseCase
{
    // construtor e atributos
    public function __construct(
        protected CastMemberRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(FindByIdCastMemberInputDto $input): FindByIdCastMemberOutputDto
    {
        // buscando a entidade no BD utilizando o repository
        $castMember = $this->repository->findById($input->id);

        // retornando os dados
        return new FindByIdCastMemberOutputDto(
            id: $castMember->id(),
            name: $castMember->name,
            type: $castMember->type->value,
            created_at: $castMember->createdAt(),
            updated_at: $castMember->updatedAt(),
        );
    }
}
