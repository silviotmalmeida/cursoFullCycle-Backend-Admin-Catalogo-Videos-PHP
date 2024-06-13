<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Entity;
use Core\Domain\Entity\Video;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface VideoRepositoryInterface
{
    public function updateMedia(Entity $entity): Entity;

    public function insert(Video $entity): Video;
}
