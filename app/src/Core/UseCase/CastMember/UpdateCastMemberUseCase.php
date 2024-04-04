<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\CastMember;

// importações
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\DTO\CastMember\UpdateCastMember\UpdateCastMemberInputDto;
use Core\UseCase\DTO\CastMember\UpdateCastMember\UpdateCastMemberOutputDto;

// definindo o usecase
class UpdateCastMemberUseCase
{
    // construtor e atributos
    public function __construct(
        protected CastMemberRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(UpdateCastMemberInputDto $input): UpdateCastMemberOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $castMember = $this->repository->findById($input->id);

        // atualizando os dados da castMember
        $castMember->update(
            name: $input->name,
            type: $input->type,
        );

        // atualizando a entidade no BD utilizando o repository
        $updatedCastMember = $this->repository->update($castMember);

        // retornando os dados
        return new UpdateCastMemberOutputDto(
            id: $updatedCastMember->id(),
            name: $updatedCastMember->name,
            type: $updatedCastMember->type->value,
            created_at: $updatedCastMember->createdAt(),
            updated_at: $updatedCastMember->updatedAt(),
        );
    }
}
