<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\DeleteById;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoInputDto;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoOutputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoInputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoOutputDto;

// definindo o usecase
class DeleteByIdVideoUseCase extends BaseVideoUseCase
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
    public function execute(DeleteByIdVideoInputDto $input): DeleteByIdVideoOutputDto
    {
        // buscando a entidade no BD com os dados do input
        $video = $this->repository->findById($input->id);

        // deletando a entidade no BD utilizando o repository
        $sucess = false;
        if ($video->id()) $sucess = $this->repository->deleteById($input->id);

        // retornando os dados
        return new DeleteByIdVideoOutputDto(
            sucess: $sucess,
        );
    }

    // método responsável por retornar o builder a ser utilizado pelo usecase
    protected function getBuilder(): ?VideoBuilderInterface
    {
        return null;
    }
}
