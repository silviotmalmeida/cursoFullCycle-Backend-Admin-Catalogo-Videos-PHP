<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

use Core\Domain\Validation\DomainValidation;

// importações


// definindo o objeto de valor
class Image
{
    // construtor e atributos
    public function __construct(
        protected string $path
    ) {

        // validando os atributos
        $this->validate();
    }

    // função de obtenção do path
    public function path(): string
    {
        return $this->path;
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do path
        DomainValidation::notNullOrEmpty($this->path);
    }
}
