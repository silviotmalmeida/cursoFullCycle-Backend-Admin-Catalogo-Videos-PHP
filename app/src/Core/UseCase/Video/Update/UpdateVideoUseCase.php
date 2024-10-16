<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Update;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Events\VideoCreatedEvent;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\DTO\UpdateVideoOutputDto;
use Exception;

// definindo o usecase
class UpdateVideoUseCase extends BaseVideoUseCase
{
    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    // o segundo argumento é para possibilitar o teste de rollback da transação
    public function execute(UpdateVideoInputDto $input, bool $simulateTransactionException = false): UpdateVideoOutputDto
    {
        // como os dados serão inseridos em mais de uma tabela,
        // o uso de transações é necessário
        // tratamento de exceções
        try {
            // validando as entidades informadas
            $this->validateAllEntitiesIds($input);
            // buscando a entidade no BD com os dados do input
            $video = $this->repository->findById($input->id);
            $originalVideo = $video;

            // atualizando a entidade com os dados do input
            $video->update(
                title: $input->title,
                description: $input->description,
                yearLaunched: $input->yearLaunched,
                duration: $input->duration,
                opened: $input->opened,
                categoriesId: $input->categoriesId,
                genresId: $input->genresId,
                castMembersId: $input->castMembersId,
                rating: $input->rating,
            );

            // inserindo a entidade no BD utilizando o repository
            $updatedVideo = $this->repository->update($video);

            // inserindo a entidade no builder
            $this->videoBuilder->setEntity($updatedVideo);

            // armazenando o thumbFile
            // se estiver setado,
            if ($input->thumbFile !== null) {
                // se foi passado um array vazio,
                if ($input->thumbFile === []) {
                    // se já existir registro no bd, atualiza a entidade, removendo o registro do arquivo
                    if ($originalVideo->thumbFile()) {
                        // atualizando a entidade
                        $this->videoBuilder->removeThumbFile();
                    }
                }
                // se foi passado um array com dados,
                else {
                    // armazenando o novo arquivo
                    $thumbFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbFile);
                    // atualizando a entidade
                    $this->videoBuilder->addThumbFile($thumbFilePath);
                }
            }

            // armazenando o thumbHalf
            // se estiver setado,
            if ($input->thumbHalf !== null) {
                // se foi passado um array vazio,
                if ($input->thumbHalf === []) {
                    // se já existir registro no bd, atualiza a entidade, removendo o registro do arquivo
                    if ($originalVideo->thumbHalf()) {
                        // atualizando a entidade
                        $this->videoBuilder->removeThumbHalf();
                    }
                }
                // se foi passado um array com dados,
                else {
                    // armazenando o novo arquivo
                    $thumbHalfPath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbHalf);
                    // atualizando a entidade
                    $this->videoBuilder->addThumbHalf($thumbHalfPath);
                }
            }

            // armazenando o bannerFile
            // se estiver setado,
            if ($input->bannerFile !== null) {
                // se foi passado um array vazio,
                if ($input->bannerFile === []) {
                    // se já existir registro no bd, atualiza a entidade, removendo o registro do arquivo
                    if ($originalVideo->bannerFile()) {
                        // atualizando a entidade
                        $this->videoBuilder->removeBannerFile();
                    }
                }
                // se foi passado um array com dados,
                else {
                    // armazenando o novo arquivo
                    $bannerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->bannerFile);
                    // atualizando a entidade
                    $this->videoBuilder->addBannerFile($bannerFilePath);
                }
            }

            // armazenando o trailerFile
            // se estiver setado,
            if ($input->trailerFile !== null) {
                // se foi passado um array vazio,
                if ($input->trailerFile === []) {
                    // se já existir registro no bd, atualiza a entidade, removendo o registro do arquivo
                    if ($originalVideo->trailerFile()) {
                        // atualizando a entidade
                        $this->videoBuilder->removeTrailerFile();
                    }
                }
                // se foi passado um array com dados,
                else {
                    // armazenando o novo arquivo
                    $trailerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->trailerFile);
                    // atualizando a entidade
                    $this->videoBuilder->addTrailerFile($trailerFilePath, MediaStatus::PROCESSING);
                }
            }

            // armazenando o videoFile
            // se estiver setado,
            if ($input->videoFile !== null) {
                // se foi passado um array vazio,
                if ($input->videoFile === []) {
                    // se já existir registro no bd, atualiza a entidade, removendo o registro do arquivo
                    if ($originalVideo->videoFile()) {
                        // atualizando a entidade
                        $this->videoBuilder->removeVideoFile();
                    }
                }
                // se foi passado um array com dados,
                else {
                    // armazenando o novo arquivo
                    $videoFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->videoFile);
                    // atualizando a entidade
                    $this->videoBuilder->addVideoFile($videoFilePath, MediaStatus::PROCESSING);
                    // dispara o evento VideoCreatedEvent
                    $this->eventManager->dispatch(new VideoCreatedEvent($this->videoBuilder->getEntity()));
                }
            }

            // atualizando o registro
            $this->repository->updateMedia($this->videoBuilder->getEntity());

            // lançando exception para testar o rollback
            if ($simulateTransactionException) throw new Exception("rollback test id:" . $this->videoBuilder->getEntity()->id());

            // em caso de sucesso, comita
            $this->transactionDb->commit();
        }
        // caso existam erros
        catch (\Throwable $th) {
            // executa o rollback
            $this->transactionDb->rollback();
            // remove os arquivos novos, se tiverem sido armazenados
            if (isset($thumbFilePath) and $thumbFilePath) $this->fileStorage->delete($thumbFilePath);
            if (isset($thumbHalfPath) and $thumbHalfPath) $this->fileStorage->delete($thumbHalfPath);
            if (isset($bannerFilePath) and $bannerFilePath) $this->fileStorage->delete($bannerFilePath);
            if (isset($trailerFilePath) and $trailerFilePath) $this->fileStorage->delete($trailerFilePath);
            if (isset($videoFilePath) and $videoFilePath) $this->fileStorage->delete($videoFilePath);
            // lança exceção
            throw $th;
        }

        // apaga os arquivos obsoletos
        // apagando o thumbFile
        // se o input for passado e já existir registro anterior no bd,
        if ($input->thumbFile !== null and $originalVideo->thumbFile()) {
            $this->removeFile($originalVideo->thumbFile()->filePath());
        }
        // apagando o thumbHalf
        // se o input for passado e já existir registro anterior no bd,
        if ($input->thumbHalf !== null and $originalVideo->thumbHalf()) {
            $this->removeFile($originalVideo->thumbHalf()->filePath());
        }
        // apagando o bannerFile
        // se o input for passado e já existir registro anterior no bd,
        if ($input->bannerFile !== null and $originalVideo->bannerFile()) {
            $this->removeFile($originalVideo->bannerFile()->filePath());
        }
        // apagando o trailerFile
        // se o input for passado e já existir registro anterior no bd,
        if ($input->trailerFile !== null and $originalVideo->trailerFile()) {
            $this->removeFile($originalVideo->trailerFile()->filePath());
            $this->removeFile($originalVideo->trailerFile()->encodedPath());
        }
        // apagando o videoFile
        // se o input for passado e já existir registro anterior no bd,
        if ($input->videoFile !== null and $originalVideo->videoFile()) {
            $this->removeFile($originalVideo->videoFile()->filePath());
            $this->removeFile($originalVideo->videoFile()->encodedPath());
        }

        // retornando os dados
        return new UpdateVideoOutputDto(
            id: $this->videoBuilder->getEntity()->id(),
            title: $this->videoBuilder->getEntity()->title,
            description: $this->videoBuilder->getEntity()->description,
            yearLaunched: $this->videoBuilder->getEntity()->yearLaunched,
            duration: $this->videoBuilder->getEntity()->duration,
            opened: $this->videoBuilder->getEntity()->opened,
            rating: $this->videoBuilder->getEntity()->rating,
            categoriesId: $this->videoBuilder->getEntity()->categoriesId,
            genresId: $this->videoBuilder->getEntity()->genresId,
            castMembersId: $this->videoBuilder->getEntity()->castMembersId,
            thumbFile: $this->videoBuilder->getEntity()->thumbFile()?->filePath(),
            thumbHalf: $this->videoBuilder->getEntity()->thumbHalf()?->filePath(),
            bannerFile: $this->videoBuilder->getEntity()->bannerFile()?->filePath(),
            trailerFile: $this->videoBuilder->getEntity()->trailerFile()?->filePath(),
            videoFile: $this->videoBuilder->getEntity()->videoFile()?->filePath(),
            created_at: $this->videoBuilder->getEntity()->createdAt(),
            updated_at: $this->videoBuilder->getEntity()->updatedAt(),
        );
    }

    // método responsável por retornar o builder a ser utilizado pelo usecase
    protected function getBuilder(): ?VideoBuilderInterface
    {
        return new CreateVideoBuilder();
    }
}
