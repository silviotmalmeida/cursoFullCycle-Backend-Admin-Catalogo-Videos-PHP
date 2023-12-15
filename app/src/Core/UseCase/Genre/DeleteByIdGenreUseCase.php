<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreInputDto;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreOutputDto;

// definindo o usecase
class DeleteByIdGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(DeleteByIdGenreInputDto $input): DeleteByIdGenreOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $Genre = $this->repository->findById($input->id);

        // deletando a entidade no BD utilizando o repository
        $sucess = false;
        if ($Genre->id()) $sucess = $this->repository->deleteById($input->id);

        // retornando os dados
        return new DeleteByIdGenreOutputDto(
            sucess: $sucess,
        );
    }
}
