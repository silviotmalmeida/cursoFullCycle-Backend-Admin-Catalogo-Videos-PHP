<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity\Traits;

// importações
use Exception;

// definindo a trait que vai possibilitar a utilização dos métodos mágicos
trait MagicMethodsTrait
{
    // implementando o get das propriedades das classes
    public function __get($property)
    {
        // se a propriedade existir, retorna o valor associado
        if ($this->{$property})
            return $this->{$property};

        // obtendo o nome da classe
        $className = get_class($this);
        // caso a propriedade não exista na classe, lança uma exceção
        throw new Exception("Property {$property} not found in {$className}");
    }
}
