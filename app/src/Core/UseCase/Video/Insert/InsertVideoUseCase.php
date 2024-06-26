<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Events\VideoCreatedEvent;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\EntityRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;
use Exception;

// definindo o usecase
class InsertVideoUseCase
{
    // atributos fora do construtor
    private Video $video;

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

            // validando as categories informadas
            $categoriesBdId = $this->validateEntitiesIds(
                listIds: $input->categoriesId,
                repository: $this->categoryRepository,
                entityNameSingular: 'Category',
                entityNamePlural: 'Categories'
            );

            // validando os genres informados
            $genresBdId = $this->validateEntitiesIds(
                listIds: $input->genresId,
                repository: $this->genreRepository,
                entityNameSingular: 'Genre',
                entityNamePlural: 'Genres'
            );

            // validando os cast members informados
            $castMembersBdId = $this->validateEntitiesIds(
                listIds: $input->castMembersId,
                repository: $this->castMemberRepository,
                entityNameSingular: 'Cast Member',
                entityNamePlural: 'Cast Members'
            );

            // criando a entidade com os dados do input
            $this->video = new Video(
                title: $input->title,
                description: $input->description,
                yearLaunched: $input->yearLaunched,
                duration: $input->duration,
                rating: $input->rating,
            );

            if ($input->opened) $this->video->open();

            // adicionando as categories
            foreach ($input->categoriesId as $categoryId) {

                $this->video->addCategoryId($categoryId);
            }

            // adicionando os genres
            foreach ($input->genresId as $genreId) {

                $this->video->addGenreId($genreId);
            }

            // adicionando os cast members
            foreach ($input->castMembersId as $castMemberId) {

                $this->video->addCastMemberId($castMemberId);
            }

            // inserindo a entidade no BD utilizando o repository
            $insertedVideo = $this->repository->insert($this->video);

            // armazenando o thumbFile
            $thumbFilePath = $this->storeFile($this->video->id(), $input->thumbFile);
            // cria o objeto de thumbFile para a entidade
            $thumbFile = new Image(
                filePath: $thumbFilePath,
            );
            // atualizando a entidade
            $this->video->setThumbFile($thumbFile);

            // armazenando o thumbHalf
            $thumbHalfPath = $this->storeFile($this->video->id(), $input->thumbHalf);
            // cria o objeto de thumbHalf para a entidade
            $thumbHalf = new Image(
                filePath: $thumbHalfPath,
            );
            // atualizando a entidade
            $this->video->setThumbHalf($thumbHalf);

            // armazenando o bannerFile
            $bannerFilePath = $this->storeFile($this->video->id(), $input->bannerFile);
            // cria o objeto de bannerFile para a entidade
            $bannerFile = new Image(
                filePath: $bannerFilePath,
            );
            // atualizando a entidade
            $this->video->setBannerFile($bannerFile);

            // armazenando o trailerFile
            $trailerFilePath = $this->storeFile($this->video->id(), $input->trailerFile);
            // cria o objeto de trailerFile para a entidade
            $trailerFile = new Media(
                filePath: $trailerFilePath,
                mediaStatus: MediaStatus::PROCESSING,
                encodedPath: ''
            );
            // atualizando a entidade
            $this->video->setTraileFile($trailerFile);

            // armazenando o arquivo
            $videoFilePath = $this->storeFile($this->video->id(), $input->videoFile);
            // se o vídeo foi armazenado,
            if ($videoFilePath) {

                // cria o objeto de videoFile para a entidade
                $videoFile = new Media(
                    filePath: $videoFilePath,
                    mediaStatus: MediaStatus::PROCESSING,
                    encodedPath: ''
                );
                // atualizando a entidade
                $this->video->setVideoFile($videoFile);

                // dispara o evento VideoCreatedEvent
                $this->eventManager->dispatch(new VideoCreatedEvent($this->video));
            }

            // atualizando o registro
            $this->repository->updateMedia($this->video);

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
            // remove os arquivos, se tiverem sido armazenados
            if (isset($thumbFilePath) and $thumbFilePath) $this->fileStorage->delete($thumbFilePath);
            if (isset($thumbHalfPath) and $thumbHalfPath) $this->fileStorage->delete($thumbHalfPath);
            if (isset($bannerFilePath) and $bannerFilePath) $this->fileStorage->delete($bannerFilePath);
            if (isset($trailerFilePath) and $trailerFilePath) $this->fileStorage->delete($trailerFilePath);
            if (isset($videoFilePath) and $videoFilePath) $this->fileStorage->delete($videoFilePath);
            // lança exceção
            throw $th;
        }
    }

    // método auxiliar para verificação de existência dos ids recebidos
    private function validateEntitiesIds(array $listIds, EntityRepositoryInterface $repository, string $entityNameSingular, string $entityNamePlural): array
    {
        // removendo duplicatas da lista
        $listIds = array_unique($listIds);
        // obtendo a lista de entidades existentes no bd
        $entitiesBd = $repository->findByIdArray($listIds);
        // coletando somente os id das entidades existentes
        $entitiesBdId = array_map(function ($n) {
            return $n->id();
        }, $entitiesBd);
        // verificando as diferenças entre as listas
        $diff = array_diff($listIds, $entitiesBdId);

        // se existem diferenças, lança exceção
        if (count($diff)) {
            // preparando a mensagem
            $msg = sprintf(
                '%s %s not found',
                count($diff) > 1 ? $entityNamePlural : $entityNameSingular,
                implode(', ', $diff)
            );
            // lança exceção
            throw new NotFoundException($msg);
        }

        return $entitiesBdId;
    }

    // método auxiliar para armazenar um arquivo
    private function storeFile(string $path, ?array $file = null): null|string
    {
        // se existir file
        if ($file) {

            // armazenando o arquivo
            $videoFilePath = $this->fileStorage->store($path, $file);

            return $videoFilePath;
        }

        return null;
    }
}
