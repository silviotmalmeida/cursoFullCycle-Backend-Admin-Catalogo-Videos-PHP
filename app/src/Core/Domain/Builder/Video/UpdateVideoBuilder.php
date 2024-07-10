<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Builder\Video;

// importações
use Core\Domain\Entity\Video;

// definindo a interface especializada de atualização da entidade Video
class UpdateVideoBuilder extends CreateVideoBuilder
{
    // método de atualização da entidade básica
    public function createEntity(object $input): CreateVideoBuilder
    {
        // criando a entidade com os dados do input
        $this->entity = new Video(
            id: $input->id,
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
}
