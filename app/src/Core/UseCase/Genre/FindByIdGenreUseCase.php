<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\FindByIdGenre\FindByIdGenreInputDto;
use Core\UseCase\DTO\Genre\FindByIdGenre\FindByIdGenreOutputDto;

// definindo o usecase
class FindByIdGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(FindByIdGenreInputDto $input): FindByIdGenreOutputDto
    {
        // buscando a entidade no BD utilizando o repository
        $Genre = $this->repository->findById($input->id);

        // retornando os dados
        return new FindByIdGenreOutputDto(
            id: $Genre->id(),
            name: $Genre->name,
            is_active: $Genre->isActive,
            categoriesId: $Genre->categoriesId,
            created_at: $Genre->createdAt(),
            updated_at: $Genre->updatedAt(),
        );
    }
}
