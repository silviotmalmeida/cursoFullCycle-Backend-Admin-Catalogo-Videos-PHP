<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Genre;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreInputDto;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreOutputDto;
use Core\UseCase\Interfaces\TransactionDbInterface;

// definindo o usecase
class InsertGenreUseCase
{
    // construtor e atributos
    public function __construct(
        protected GenreRepositoryInterface $repository,
        protected TransactionDbInterface $transactionDb,
        protected CategoryRepositoryInterface $categoryRepository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(InsertGenreInputDto $input): InsertGenreOutputDto
    {
        // como os dados serão inseridos em mais de uma tabela,
        // o uso de transações é necessário
        // tratamento de exceções
        try {
            // criando a entidade com os dados do input
            $Genre = new Genre(
                name: $input->name,
                isActive: $input->isActive,
                categoriesId: $input->categoriesId
            );
            // validando as categorias informadas
            $this->validateCategoriesIds($input->categoriesId);
            // inserindo a entidade no BD utilizando o repository
            $insertedGenre = $this->repository->insert($Genre);
            // em caso de sucesso, comita
            $this->transactionDb->commit();

            // retornando os dados
            return new InsertGenreOutputDto(
                id: $insertedGenre->id(),
                name: $insertedGenre->name,
                is_active: $insertedGenre->isActive,
                categories_id: $input->categoriesId,
                created_at: $insertedGenre->createdAt(),
                updated_at: $insertedGenre->updatedAt(),
            );
        }
        // caso existam erros
        catch (\Throwable $th) {
            // executa o rollback
            $this->transactionDb->rollback();
            // lança exceção
            throw $th;
        }
    }

    // método auxiliar para verificação de existência das categoriesId recebidas
    private function validateCategoriesIds(array $listIds): void
    {
        // removendo duplicatas da lista
        $listIds = array_unique($listIds);
        // obtendo a lista de categorias existentes no bd
        $categoriesBd = $this->categoryRepository->findByIdArray($listIds);
        // coletando somente os id das categorias existentes
        $categoriesBdId = array_map(function ($n) {
            return $n->id();
        }, $categoriesBd);
        // verificando as diferenças entre as listas
        $diff = array_diff($listIds, $categoriesBdId);
        // se existem diferenças, lança exceção
        if (count($diff)) {
            // preparando a mensagem
            $msg = sprintf(
                '%s %s not found',
                count($diff) > 1 ? 'Categories' : 'Category',
                implode(', ', $diff)
            );
            // lança exceção
            throw new NotFoundException($msg);
        }
    }
}
