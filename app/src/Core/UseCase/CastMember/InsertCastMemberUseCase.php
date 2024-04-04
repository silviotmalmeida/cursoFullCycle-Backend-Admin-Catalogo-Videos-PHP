<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\CastMember;

// importações
use Core\Domain\Entity\CastMember;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\DTO\CastMember\InsertCastMember\InsertCastMemberInputDto;
use Core\UseCase\DTO\CastMember\InsertCastMember\InsertCastMemberOutputDto;

// definindo o usecase
class InsertCastMemberUseCase
{
    // construtor e atributos
    public function __construct(
        protected CastMemberRepositoryInterface $repository,
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(InsertCastMemberInputDto $input): InsertCastMemberOutputDto
    {
        // criando a entidade com os dados do input
        $CastMember = new CastMember(
            name: $input->name,
            type: $input->type,
        );

        // inserindo a entidade no BD utilizando o repository
        $insertedCastMember = $this->repository->insert($CastMember);

        // retornando os dados
        return new InsertCastMemberOutputDto(
            id: $insertedCastMember->id(),
            name: $insertedCastMember->name,
            type: $insertedCastMember->type->value,
            created_at: $insertedCastMember->createdAt(),
            updated_at: $insertedCastMember->updatedAt(),
        );
    }
}
