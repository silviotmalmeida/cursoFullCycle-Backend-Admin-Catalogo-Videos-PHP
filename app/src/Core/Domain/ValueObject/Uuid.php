<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações
use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

// definindo o objeto de valor
class Uuid extends ValueObject
{
    // construtor e atributos
    public function __construct(
        protected string $value
    ) {
        // incluindo as regras do médoto de criação da classe-mãe
        parent::__construct();

        // validando os atributos
        $this->validate();
    }

    // função de criação de um novo uuid randômico
    public static function random(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    // função de impressão do uuid
    public function __toString(): string
    {
        return $this->value;
    }

    // função de validação dos atributos
    private function validate(): void
    {
        if (!RamseyUuid::isValid($this->value)) {
            $classname = static::class;
            throw new InvalidArgumentException("{$classname} does not allow the value {$this->value}");
        }
    }
}
