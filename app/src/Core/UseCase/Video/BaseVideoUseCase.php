<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video;

// importações
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\EntityRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;

// definindo o usecase base
abstract class BaseVideoUseCase
{
    // atributos fora do construtor
    protected ?VideoBuilderInterface $videoBuilder;

    // métodos abstratos a serem implementados nas classes filhas
    abstract protected function getBuilder(): ?VideoBuilderInterface;

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
        // criando o builder da entidade video
        $this->videoBuilder = $this->getBuilder();
    }

    // método auxiliar para verificação de existência dos ids recebidos para uma entidade
    private function validateEntitiesIds(array $listIds, EntityRepositoryInterface $repository, string $entityNameSingular, string $entityNamePlural): void
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
    }

    // método auxiliar para verificação de existência dos ids recebidos para todas as entidades
    protected function validateAllEntitiesIds(object $input): void
    {
        // validando as categories informadas
        if ($input->categoriesId) {
            $this->validateEntitiesIds(
                listIds: $input->categoriesId,
                repository: $this->categoryRepository,
                entityNameSingular: 'Category',
                entityNamePlural: 'Categories'
            );
        }

        // validando os genres informados
        if ($input->genresId) {
            $this->validateEntitiesIds(
                listIds: $input->genresId,
                repository: $this->genreRepository,
                entityNameSingular: 'Genre',
                entityNamePlural: 'Genres'
            );
        }

        // validando os cast members informados
        if ($input->castMembersId) {
            $this->validateEntitiesIds(
                listIds: $input->castMembersId,
                repository: $this->castMemberRepository,
                entityNameSingular: 'Cast Member',
                entityNamePlural: 'Cast Members'
            );
        }
    }

    // método auxiliar para armazenar um arquivo
    protected function storeFile(string $path, ?array $file = null): null|string
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
