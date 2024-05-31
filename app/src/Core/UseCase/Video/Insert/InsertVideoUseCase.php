<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
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

        // protected CategoryRepositoryInterface $categoryRepository
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
            $video = new Video(
                title: $input->title,
                description: $input->description,
                yearLaunched: $input->yearLaunched,
                duration: $input->duration,
                opened: $input->opened,
                rating: $input->rating,
            );

            // adicionando as categories
            foreach ($input->categoriesId as $categoryId) {
                
                $video->addCategory($categoryId);
            }
            // validando as categories informadas
            $this->validateCategoriesIds($video->categoriesId);

            // adicionando os genres
            foreach ($input->genresId as $genreId) {
                
                $video->addGenre($genreId);
            }
            // validando os genres informados
            $this->validateGenresIds($video->genresId);

            // adicionando os cast members
            foreach ($input->castMembersId as $castMemberId) {
                
                $video->addCastMember($castMemberId);
            }
            // validando os cast members informados
            $this->validateCastMembersIds($video->castMembersId);

            // inserindo a entidade no BD utilizando o repository
            $insertedVideo = $this->repository->insert($video);

            // lançando exception para testar o rollback
            if ($simulateTransactionException) throw new Exception('rollback test');

            // em caso de sucesso, comita
            $this->transactionDb->commit();

            // retornando os dados
            return new InsertVideoOutputDto(
                id: $insertedVideo->id(),
                title: $insertedVideo->title,
                description: $insertedVideo->description,
                yearLaunched: $input->yearLaunched,
                duration: $input->duration,
                opened: $input->opened,
                rating: $input->rating,
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

    // método auxiliar para verificação de existência dos genresId recebidos
    private function validateGenresIds(array $listIds): void
    {
        // removendo duplicatas da lista
        $listIds = array_unique($listIds);
        // obtendo a lista de genres existentes no bd
        $genresBd = $this->genreRepository->findByIdArray($listIds);
        // coletando somente os id dos genres existentes
        $genresBdId = array_map(function ($n) {
            return $n->id();
        }, $genresBd);
        // verificando as diferenças entre as listas
        $diff = array_diff($listIds, $genresBdId);
        // se existem diferenças, lança exceção
        if (count($diff)) {
            // preparando a mensagem
            $msg = sprintf(
                '%s %s not found',
                count($diff) > 1 ? 'Genres' : 'Genre',
                implode(', ', $diff)
            );
            // lança exceção
            throw new NotFoundException($msg);
        }
    }

    // método auxiliar para verificação de existência dos castMembersId recebidos
    private function validateCastMembersIds(array $listIds): void
    {
        // removendo duplicatas da lista
        $listIds = array_unique($listIds);
        // obtendo a lista de cast members existentes no bd
        $castMembersBd = $this->castMemberRepository->findByIdArray($listIds);
        // coletando somente os id dos cast members existentes
        $castMembersBdId = array_map(function ($n) {
            return $n->id();
        }, $castMembersBd);
        // verificando as diferenças entre as listas
        $diff = array_diff($listIds, $castMembersBdId);
        // se existem diferenças, lança exceção
        if (count($diff)) {
            // preparando a mensagem
            $msg = sprintf(
                '%s %s not found',
                count($diff) > 1 ? 'Cast Members' : 'Cast Member',
                implode(', ', $diff)
            );
            // lança exceção
            throw new NotFoundException($msg);
        }
    }
}
