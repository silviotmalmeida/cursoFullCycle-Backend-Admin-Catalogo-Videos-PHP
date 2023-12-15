<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreInputDto;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreOutputDto;

// definindo o usecase
class InsertGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(InsertGenreInputDto $input): InsertGenreOutputDto
    {
        // criando a entidade com os dados do input
        $Genre = new Genre(
            name: $input->name,
            isActive: $input->isActive,
            categoriesId: $input->categoriesId
        );

        // inserindo a entidade no BD utilizando o repository
        $insertedGenre = $this->repository->insert($Genre);

        // retornando os dados
        return new InsertGenreOutputDto(
            id: $insertedGenre->id(),
            name: $insertedGenre->name,
            is_active: $insertedGenre->isActive,
            categories_id: $insertedGenre->categoriesId,
            created_at: $insertedGenre->createdAt(),
            updated_at: $insertedGenre->updatedAt(),
        );
    }
}
