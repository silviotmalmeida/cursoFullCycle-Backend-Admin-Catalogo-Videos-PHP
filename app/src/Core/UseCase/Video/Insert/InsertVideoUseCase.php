<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\DTO\Video\InsertVideo\InsertVideoInputDto;
use Core\UseCase\DTO\Video\InsertVideo\InsertVideoOutputDto;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;
use Exception;

// definindo o usecase
class InsertVideoUseCase
{
    // construtor e atributos
    public function __construct(
        protected VideoRepositoryInterface $repository,
        protected TransactionDbInterface $transactionDb,
        protected FileStorageInterface $fileStorage,
        protected VideoEventManagerInterface $eventManager,

        protected CategoryRepositoryInterface $categoryRepository
    ) {
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    // o segundo argumento é para possibilitar o teste de rollback da transação
    public function execute(InsertVideoInputDto $input, bool $simulateTransactionException = false): InsertVideoOutputDto
    {
        // como os dados serão inseridos em mais de uma tabela,
        // o uso de transações é necessário
        // tratamento de exceções
        try {
            // criando a entidade com os dados do input
            $Video = new Video(
                name: $input->name,
                isActive: $input->isActive,
                categoriesId: $input->categoriesId
            );
            // validando as categorias informadas
            $this->validateCategoriesIds($input->categoriesId);
            // inserindo a entidade no BD utilizando o repository
            $insertedVideo = $this->repository->insert($Video);

            // lançando exception para testar o rollback
            if ($simulateTransactionException) throw new Exception('rollback test');

            // em caso de sucesso, comita
            $this->transactionDb->commit();

            // retornando os dados
            return new InsertVideoOutputDto(
                id: $insertedVideo->id(),
                name: $insertedVideo->name,
                is_active: $insertedVideo->isActive,
                categories_id: $input->categoriesId,
                created_at: $insertedVideo->createdAt(),
                updated_at: $insertedVideo->updatedAt(),
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
