<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\FindById;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoInputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoOutputDto;

// definindo o usecase
class FindByIdVideoUseCase extends BaseVideoUseCase
{
    // construtor e atributos
    public function __construct(
        protected VideoRepositoryInterface $repository
    ) {
        // criando o builder da entidade video
        $this->videoBuilder = $this->getBuilder();
    }

    // método de execução do usecase
    // recebe um inputDto e retorna um outputDto
    public function execute(FindByIdVideoInputDto $input): FindByIdVideoOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $video = $this->repository->findById($input->id);

        // inserindo a entidade no builder
        $this->videoBuilder->setEntity($video);

        // retornando os dados
        return new FindByIdVideoOutputDto(
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
