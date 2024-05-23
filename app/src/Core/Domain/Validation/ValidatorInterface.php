<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Validation;

// importações
use Core\Domain\Entity\Entity;
use Core\Domain\ValueObject\ValueObject;

// definindo a interface a ser implementada pelos validadores  das entidades e objetos de valor
interface ValidatorInterface
{
    public function validate(Entity|ValueObject $object): void;
}
