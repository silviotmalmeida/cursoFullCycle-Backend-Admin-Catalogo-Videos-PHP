<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\CastMember;

// importações
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\DTO\CastMember\DeleteByIdCastMember\DeleteByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\DeleteByIdCastMember\DeleteByIdCastMemberOutputDto;

// definindo o usecase
class DeleteByIdCastMemberUseCase
{
    // construtor e atributos
    public function __construct(
        protected CastMemberRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(DeleteByIdCastMemberInputDto $input): DeleteByIdCastMemberOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $castMember = $this->repository->findById($input->id);

        // deletando a entidade no BD utilizando o repository
        $sucess = false;
        if ($castMember->id()) $sucess = $this->repository->deleteById($input->id);

        // retornando os dados
        return new DeleteByIdCastMemberOutputDto(
            sucess: $sucess,
        );
    }
}
