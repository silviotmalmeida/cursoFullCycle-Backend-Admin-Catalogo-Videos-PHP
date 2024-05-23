<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações
use Core\Domain\Validation\DomainValidation;

// definindo o objeto de valor
class Image extends ValueObject
{
    // construtor e atributos
    public function __construct(
        protected string $path
    ) {
        // incluindo as regras do médoto de criação da classe-mãe
        parent::__construct();

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
