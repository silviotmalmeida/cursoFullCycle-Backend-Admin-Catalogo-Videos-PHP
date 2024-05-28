<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Repository;

// importações
use Core\Domain\Entity\Entity;

// definindo a interface de comunicação com a entidade
// contém a descrição dos métodos a serem implementados no repository
interface VideoRepositoryInterface extends EntityRepositoryInterface
{
    public function updateMedia(Entity $entity): Entity;
}
