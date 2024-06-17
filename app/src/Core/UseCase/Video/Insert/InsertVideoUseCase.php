<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Events\VideoCreatedEvent;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
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
        protected CategoryRepositoryInterface $categoryRepository,
        protected GenreRepositoryInterface $genreRepository,
        protected CastMemberRepositoryInterface $castMemberRepository,
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
                thumbFile: $input->thumbFile,
                thumbHalf: $input->thumbHalf,
                bannerFile: $input->bannerFile,
                trailerFile: $input->trailerFile,
                videoFile: $input->videoFile,
            );

            // validando as categories informadas
            $categoriesBdId = $this->validateCategoriesIds($input->categoriesId);
            // adicionando as categories
            foreach ($input->categoriesId as $categoryId) {

                $video->addCategory($categoryId);
            }

            // validando os genres informados
            $genresBdId = $this->validateGenresIds($input->genresId);
            // adicionando os genres
            foreach ($input->genresId as $genreId) {

                $video->addGenre($genreId);
            }

            // validando os cast members informados
            $castMembersBdId = $this->validateCastMembersIds($input->castMembersId);
            // adicionando os cast members
            foreach ($input->castMembersId as $castMemberId) {

                $video->addCastMember($castMemberId);
            }

            // armazenando o arquivo
            $videoFilePath = $this->fileStorage->store($video->id(), [$video->videoFile()]);

            // se o vídeo foi armazenado, dispara o evento VideoCreatedEvent
            if ($videoFilePath) $this->eventManager->dispatch(new VideoCreatedEvent($video));

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
                yearLaunched: $insertedVideo->yearLaunched,
                duration: $insertedVideo->duration,
                opened: $insertedVideo->opened,
                rating: $insertedVideo->rating,
                categoriesId: $categoriesBdId,
                genresId: $genresBdId,
                castMembersId: $castMembersBdId,
                thumbFile: $insertedVideo->thumbFile()?->filePath(),
                thumbHalf: $insertedVideo->thumbHalf()?->filePath(),
                bannerFile: $insertedVideo->bannerFile()?->filePath(),
                trailerFile: $insertedVideo->trailerFile()?->filePath(),
                videoFile: $insertedVideo->videoFile()?->filePath(),
                created_at: $insertedVideo->createdAt(),
                updated_at: $insertedVideo->updatedAt(),
            );
        }
        // caso existam erros
        catch (\Throwable $th) {
            // executa o rollback
            $this->transactionDb->rollback();
            // remove o arquivo, se tiver sido armazenado
            if (isset($videoFilePath) and $videoFilePath) $this->fileStorage->delete($videoFilePath);
            // lança exceção
            throw $th;
        }
    }

    // método auxiliar para verificação de existência das categoriesId recebidas
    private function validateCategoriesIds(array $listIds): array
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

        return $categoriesBdId;
    }

    // método auxiliar para verificação de existência dos genresId recebidos
    private function validateGenresIds(array $listIds): array
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

        return $genresBdId;
    }

    // método auxiliar para verificação de existência dos castMembersId recebidos
    private function validateCastMembersIds(array $listIds): array
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

        return $castMembersBdId;
    }
}
