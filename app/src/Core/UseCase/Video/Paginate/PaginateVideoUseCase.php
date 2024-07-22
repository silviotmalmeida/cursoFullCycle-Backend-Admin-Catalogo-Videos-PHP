<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Paginate;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\BaseVideoUseCase;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoInputDto;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoOutputDto;

// definindo o usecase
class PaginateVideoUseCase extends BaseVideoUseCase
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
    public function execute(PaginateVideoInputDto $input): PaginateVideoOutputDto
    {
        // buscando as entidades no BD utilizando o repository
        $pagination = $this->repository->paginate(
            filter: $input->filter,
            order: $input->order,
            page: $input->page,
            perPage: $input->perPage
        );

        // retornando os dados
        return new PaginateVideoOutputDto(
            items: $pagination->items(),
            total: $pagination->total(),
            last_page: $pagination->lastPage(),
            first_page: $pagination->firstPage(),
            current_page: $pagination->currentPage(),
            per_page: $pagination->perPage(),
            to: $pagination->to(),
            from: $pagination->from(),
        );
    }

    // método responsável por retornar o builder a ser utilizado pelo usecase
    protected function getBuilder(): ?VideoBuilderInterface
    {
        return null;
    }
}
