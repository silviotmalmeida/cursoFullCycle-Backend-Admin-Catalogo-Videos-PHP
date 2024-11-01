<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\UpdateEncodedVideoPath;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Events\VideoCreatedEvent;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\DTO\UpdateVideoOutputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathInputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathOutputDto;
use Exception;

// definindo o usecase
class UpdateEncodedVideoPathUseCase
{
    // atributos fora do construtor
    protected ?VideoBuilderInterface $videoBuilder;

    // construtor e atributos
    public function __construct(
        protected VideoRepositoryInterface $repository,
    ) {
        // criando o builder da entidade video
        $this->videoBuilder = $this->getBuilder();
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(UpdateEncodedVideoPathInputDto $input): UpdateEncodedVideoPathOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $video = $this->repository->findById($input->id);

        // inserindo a entidade no builder
        $this->videoBuilder->setEntity($video);

        // se estiver setado encodedPath,
        if ($input->encodedPath) {
            // se existir video na entidade
            if ($video->videoFile()) {
                // atualizando a entidade
                $this->videoBuilder->addVideoFile($video->videoFile()->filePath(), MediaStatus::COMPLETE, $input->encodedPath);
                // atualizando o BD
                $this->repository->updateMedia($this->videoBuilder->getEntity());
            }
        }

        // retornando os dados
        return new UpdateEncodedVideoPathOutputDto(
            id: $video->id(),
            encodedPath: $video->videoFile() ? $video->videoFile()->encodedPath() : null,
        );
    }

    // método responsável por retornar o builder a ser utilizado pelo usecase
    protected function getBuilder(): ?VideoBuilderInterface
    {
        return new CreateVideoBuilder();
    }
}
