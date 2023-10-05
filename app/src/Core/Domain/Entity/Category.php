<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Uuid;

// definindo a entidade
class Category
{
    // incluindo a trait que ativa os métodos mágicos
    use MagicMethodsTrait;

    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $name = '',
        protected string $description = '',
        protected bool $isActive = true,
    ) {
        // se o id for vazio, atribui um uuid randomicamente
        if ($this->id == '') {
            $this->id = Uuid::random();
        }
        // senão, converte a string recebida para um objeto de valor uuid
        else {
            $this->id = new Uuid($this->id);
        }

        // validando os atributos
        $this->validate();
    }

    // função de ativação
    public function activate(): void
    {
        $this->isActive = true;
    }

    // função de desativação
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    // função de atualização dos atributos possíveis
    public function update(
        string $name = '',
        string $description = '',
    ): void {
        // atualiza somente os atributos com valores recebidos
        if ($name) $this->name = $name;
        if ($description) $this->description = $description;

        // validando os atributos
        $this->validate();
    }

    // função de validação dos atributos
    private function validate(): void
    {
        DomainValidation::notNullOrEmpty($this->name);
        DomainValidation::strMaxLenght($this->name);
        DomainValidation::strMinLenght($this->name);

        DomainValidation::strNullOrMaxLength($this->description);
        DomainValidation::strNullOrMixLength($this->description);
    }
}
