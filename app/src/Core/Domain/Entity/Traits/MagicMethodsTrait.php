<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity\Traits;

// importações
use Exception;

// definindo a trait que vai possibilitar a utilização dos métodos mágicos
trait MagicMethodsTrait
{
    // implementando o get dos atributos das classes
    public function __get($property)
    {
        // se o atributo existir, retorna o valor associado
        if (isset($this->{$property}))
            return $this->{$property};

        // obtendo o nome da classe
        $className = get_class($this);
        // caso o atributo não exista na classe, lança uma exceção
        throw new Exception("Property {$property} not found in {$className}");
    }

    // função que retorna o valor do uuid como string
    public function id(): string
    {
        return (string) $this->id;
    }
}
