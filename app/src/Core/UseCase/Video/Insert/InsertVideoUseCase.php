<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Insert;

// importações
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Events\VideoCreatedEvent;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
use Exception;

// definindo o usecase
class InsertVideoUseCase extends BaseVideoUseCase
{
    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    // o segundo argumento é para possibilitar o teste de rollback da transação
    public function execute(InsertVideoInputDto $input, bool $simulateTransactionException = false): InsertVideoOutputDto
    {
        // como os dados serão inseridos em mais de uma tabela,
        // o uso de transações é necessário
        // tratamento de exceções
        try {

            // validando as entidades informadas
            $this->validateAllEntitiesIds($input);
            
            // criando a entidade com os dados do input
            $this->videoBuilder->createEntity($input);

            // inserindo a entidade no BD utilizando o repository
            $insertedVideo = $this->repository->insert($this->videoBuilder->getEntity());

            // armazenando o thumbFile
            $thumbFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbFile);
            // cria o objeto de thumbFile para a entidade
            $this->videoBuilder->addThumbFile($thumbFilePath);

            // armazenando o thumbHalf
            $thumbHalfPath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbHalf);
            // cria o objeto de thumbHalf para a entidade
            $this->videoBuilder->addThumbHalf($thumbHalfPath);

            // armazenando o bannerFile
            $bannerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->bannerFile);
            // cria o objeto de bannerFile para a entidade
            $this->videoBuilder->addBannerFile($bannerFilePath);

            // armazenando o trailerFile
            $trailerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->trailerFile);
            // cria o objeto de trailerFile para a entidade
            $this->videoBuilder->addTrailerFile($trailerFilePath, MediaStatus::PROCESSING);

            // armazenando o arquivo
            $videoFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->videoFile);
            // se o vídeo foi armazenado,
            if ($videoFilePath) {

                // cria o objeto de videoFile para a entidade
                $this->videoBuilder->addVideoFile($videoFilePath, MediaStatus::PROCESSING);

                // dispara o evento VideoCreatedEvent
                $this->eventManager->dispatch(new VideoCreatedEvent($this->videoBuilder->getEntity()));
            }

            // atualizando o registro
            $this->repository->updateMedia($this->videoBuilder->getEntity());

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
                categoriesId: $insertedVideo->categoriesId,
                genresId: $insertedVideo->genresId,
                castMembersId: $insertedVideo->castMembersId,
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
}
