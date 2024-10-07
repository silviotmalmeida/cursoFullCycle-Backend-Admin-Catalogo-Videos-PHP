<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Uuid;
use DateTime;

// definindo a entidade
class Category extends Entity
{
    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $name = '',
        protected string $description = '',
        protected bool $isActive = true,
        protected DateTime|string $createdAt = '',
        protected DateTime|string $updatedAt = '',
    ) {
        // incluindo as regras do médoto de criação da classe-mãe
        parent::__construct();

        // processamento do id
        // se o id for vazio, atribui um uuid randomicamente
        if ($this->id == '') {
            $this->id = Uuid::random();
        }
        // senão, converte a string recebida para um objeto de valor uuid
        else {
            $this->id = new Uuid($this->id);
        }

        // processamento do createdAt
        // se o createdAt for vazio, atribui a data atual
        if ($this->createdAt == '') {
            $this->createdAt = new DateTime();
        }
        // senão, converte a string recebida para um Datetime
        else {
            $this->createdAt = new DateTime($this->createdAt);
        }

        // processamento do updatedAt
        // se o updatedAt for vazio, atribui a data de criação
        if ($this->updatedAt == '') {
            $this->updatedAt = $this->createdAt;
        }
        // senão, converte a string recebida para um Datetime
        else {
            $this->updatedAt = new DateTime($this->updatedAt);
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
        ?string $name = null,
        ?string $description = null,
        ?bool $isActive = null
    ): void {
        // atualiza somente os atributos com valores recebidos
        if (isset($name)) $this->name = $name;
        if (isset($description)) $this->description = $description;
        if (isset($isActive)) {
            if ($isActive === true) {
                $this->activate();
            } else if ($isActive === false) {
                $this->deactivate();
            }
        }

        // atualiza o updatedAt com a data atual
        if (isset($name) or isset($description) or isset($isActive)) $this->updatedAt = new DateTime();

        // validando os atributos
        $this->validate();
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do name
        DomainValidation::notNullOrEmpty($this->name);
        DomainValidation::strMaxLenght($this->name);
        DomainValidation::strMinLenght($this->name);
        // validação do description
        DomainValidation::strNullOrMaxLength($this->description);
        DomainValidation::strNullOrMinLength($this->description);
    }
}
