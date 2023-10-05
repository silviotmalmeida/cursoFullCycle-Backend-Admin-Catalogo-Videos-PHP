<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações
use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

// definindo o objeto de valor
class Uuid
{
    // construtor e atributos
    public function __construct(
        protected string $value
    ) {
        // validando os atributos
        $this->validate($value);
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
    private function validate(string $id): void
    {
        if (!RamseyUuid::isValid($id)) {
            $classname = static::class;
            throw new InvalidArgumentException("{$classname} does not allow the value {$id}");
        }
    }
}
