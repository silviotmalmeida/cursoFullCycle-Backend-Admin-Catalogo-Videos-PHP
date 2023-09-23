<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Exception\EntityValidationException;

// definindo a classe
class Category
{
    // incluindo a trait que ativa os métodos mágicos
    use MagicMethodsTrait;

    // construtor e atributos
    public function __construct(
        protected string $id = '',
        protected string $name = '',
        protected string $description = '',
        protected bool $isActive = true,
    ) {
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
        if (empty($this->name)) throw new EntityValidationException("Name cannot be empty");
        if (
            !empty($this->description) and
            (strlen($this->description) < 3 or
                strlen($this->description) > 50)
        ) throw new EntityValidationException("Description must be empty or have lenght between 3 and 50");
    }
}
