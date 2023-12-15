<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreInputDto;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreOutputDto;

// definindo o usecase
class UpdateGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(UpdateGenreInputDto $input): UpdateGenreOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $Genre = $this->repository->findById($input->id);

        // atualizando os dados da Genre
        $Genre->update($input->name, $input->isActive, $input->categoriesID);

        // atualizando a entidade no BD utilizando o repository
        $updatedGenre = $this->repository->update($Genre);

        // retornando os dados
        return new UpdateGenreOutputDto(
            id: $updatedGenre->id(),
            name: $updatedGenre->name,
            is_active: $updatedGenre->isActive,
            categories_id: $updatedGenre->categoriesId,
            created_at: $updatedGenre->createdAt(),
            updated_at: $updatedGenre->updatedAt(),
        );
    }
}
