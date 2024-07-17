<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Builder\Video;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;

// definindo a interface especializada de construção da entidade Video
class CreateVideoBuilder implements VideoBuilderInterface
{

    // atributos fora do construtor
    protected ?Video $entity = null;

    // construtor e atributos
    public function __construct()
    {
        $this->reset();
    }

    // método que limpa a entidade
    protected function reset(): void
    {
        $this->entity = null;
    }

    // método de criação da entidade básica
    public function createEntity(object $input): CreateVideoBuilder
    {
        // criando a entidade com os dados do input
        $this->entity = new Video(
            title: $input->title,
            description: $input->description,
            yearLaunched: $input->yearLaunched,
            duration: $input->duration,
            rating: $input->rating,
        );
        if ($input->opened) $this->entity->open();

        // adicionando as categories
        foreach ($input->categoriesId as $categoryId) {

            $this->entity->addCategoryId($categoryId);
        }

        // adicionando os genres
        foreach ($input->genresId as $genreId) {

            $this->entity->addGenreId($genreId);
        }

        // adicionando os cast members
        foreach ($input->castMembersId as $castMemberId) {

            $this->entity->addCastMemberId($castMemberId);
        }

        return $this;
    }

    // método de inclusão do thumbFile
    public function addThumbFile(string $path): CreateVideoBuilder
    {
        // cria o objeto de thumbFile para a entidade
        $thumbFile = new Image(
            filePath: $path,
        );
        // atualizando a entidade
        $this->entity->setThumbFile($thumbFile);

        return $this;
    }

    // método de inclusão do thumbHalf
    public function addThumbHalf(string $path): CreateVideoBuilder
    {
        // cria o objeto de thumbHalf para a entidade
        $thumbHalf = new Image(
            filePath: $path,
        );
        // atualizando a entidade
        $this->entity->setThumbHalf($thumbHalf);

        return $this;
    }

    // método de inclusão do bannerFile
    public function addBannerFile(string $path): CreateVideoBuilder
    {
        // cria o objeto de bannerFile para a entidade
        $bannerFile = new Image(
            filePath: $path,
        );
        // atualizando a entidade
        $this->entity->setBannerFile($bannerFile);

        return $this;
    }

    // método de inclusão do trailerFile
    public function addTrailerFile(string $path, MediaStatus $mediaStatus): CreateVideoBuilder
    {
        // cria o objeto de trailerFile para a entidade
        $trailerFile = new Media(
            filePath: $path,
            mediaStatus: $mediaStatus,
            encodedPath: ''
        );
        // atualizando a entidade
        $this->entity->setTraileFile($trailerFile);

        return $this;
    }

    // método de inclusão do videoFile
    public function addVideoFile(string $path, MediaStatus $mediaStatus): CreateVideoBuilder
    {
        // cria o objeto de videoFile para a entidade
        $videoFile = new Media(
            filePath: $path,
            mediaStatus: $mediaStatus,
            encodedPath: ''
        );
        // atualizando a entidade
        $this->entity->setVideoFile($videoFile);

        return $this;
    }

    // método de retorno da entidade
    public function getEntity(): Video
    {
        return $this->entity;
    }
}
