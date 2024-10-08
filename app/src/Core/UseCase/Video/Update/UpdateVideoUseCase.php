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
            if ($input->thumbFile) {
                $thumbFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbFile);
                // cria o objeto de thumbFile para a entidade
                $this->videoBuilder->addThumbFile($thumbFilePath);
            }

            // armazenando o thumbHalf
            if ($input->thumbHalf) {
                $thumbHalfPath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->thumbHalf);
                // cria o objeto de thumbHalf para a entidade
                $this->videoBuilder->addThumbHalf($thumbHalfPath);
            }

            // armazenando o bannerFile
            if ($input->bannerFile) {
                $bannerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->bannerFile);
                // cria o objeto de bannerFile para a entidade
                $this->videoBuilder->addBannerFile($bannerFilePath);
            }

            // armazenando o trailerFile
            // se estiver setado e já existir registro no bd, atualiza a entidade e o arquivo, apagando o arquivo anterior
            if ($input->trailerFile and $video->trailerFile()) {
                // removendo arquivos obsoletos
                $this->removeFile($video->trailerFile()->filePath());
                $this->removeFile($video->trailerFile()->encodedPath());
                // armazenando o novo arquivo
                $trailerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->trailerFile);
                // cria o objeto de trailerFile para a entidade
                $this->videoBuilder->addTrailerFile($trailerFilePath, MediaStatus::PROCESSING);
            }
            // se estiver setado e não existir registro no bd, atualiza a entidade e o armazena o arquivo
            else if ($input->trailerFile and !$video->trailerFile()) {
                // armazenando o novo arquivo
                $trailerFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->trailerFile);
                // cria o objeto de trailerFile para a entidade
                $this->videoBuilder->addTrailerFile($trailerFilePath, MediaStatus::PROCESSING);
            }
            // se não estiver setado e já existir registro no bd, atualiza a entidade e apaga o arquivo anterior
            else if (!$input->trailerFile and $video->trailerFile()) {
                // removendo arquivos obsoletos
                $this->removeFile($video->trailerFile()->filePath());
                $this->removeFile($video->trailerFile()->encodedPath());
                // remove o objeto de trailerFile para a entidade
                $this->videoBuilder->removeTrailerFile();
            }
            // senão, não faz nada
            else {
            }

            // armazenando o videoFile
            if ($input->videoFile) {
                $videoFilePath = $this->storeFile($this->videoBuilder->getEntity()->id(), $input->videoFile);
                // se o vídeo foi armazenado,
                if ($videoFilePath) {

                    // cria o objeto de videoFile para a entidade
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

    // método responsável por retornar o builder a ser utilizado pelo usecase
    protected function getBuilder(): ?VideoBuilderInterface
    {
        return new CreateVideoBuilder();
    }
}
