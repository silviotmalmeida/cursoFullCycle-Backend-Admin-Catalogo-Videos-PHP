<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Uuid;
use DateTime;

// definindo a entidade
class CastMember
{
    // incluindo a trait que ativa os métodos mágicos
    use MagicMethodsTrait;

    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $name = '',
        protected CastMemberType|int $type = 0,
        protected DateTime|string $createdAt = '',
        protected DateTime|string $updatedAt = '',
    ) {
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
        // se o updatedAt for vazio, atribui a data atual
        if ($this->updatedAt == '') {
            $this->updatedAt = new DateTime();
        }
        // senão, converte a string recebida para um Datetime
        else {
            $this->updatedAt = new DateTime($this->updatedAt);
        }

        // validando os atributos
        $this->validate();
    }

    // função de atualização dos atributos possíveis
    public function update(
        ?string $name = null,
        CastMemberType|int $type = null,
    ): void {
        // atualiza somente os atributos com valores recebidos
        if (isset($name)) $this->name = $name;
        if (isset($type)) $this->type = $type;

        // atualiza o updatedAt com a data atual
        if (isset($name) or isset($type)) $this->updatedAt = new DateTime();

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

        // validação do type
        if (is_int($this->type)) {
            DomainValidation::isCastMemberTypeCompatible($this->type);
            if (CastMemberType::tryFrom($this->type)) $this->type = CastMemberType::from($this->type);
        }
    }
}
