<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Validation;

// importações

use Core\Domain\Enum\CastMemberType;
use Core\Domain\Exception\EntityValidationException;

// definindo a classe
// classe com  métodos genéricos de validação
class DomainValidation
{

    // valida se um valor não está nulo ou vazio
    public static function notNullOrEmpty(mixed $value, string $exceptMessage = null): void
    {
        // se for nulo ou vazio, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (empty($value)) throw new EntityValidationException($exceptMessage ?? 'Value must not be null or empty');
    }

    // valida se a quantidade de caracteres está menor ou igual ao número máximo permitido
    public static function strMaxLenght(string $value, int $lenght = 255, string $exceptMessage = null): void
    {
        // se possuir quantidade de caracteres está maior do que o número máximo permitido, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (strlen($value) > $lenght) throw new EntityValidationException($exceptMessage ?? "Value must not be greater than {$lenght} characters");
    }

    // valida se a quantidade de caracteres está maior ou igual ao número mínimo permitido
    public static function strMinLenght(string $value, int $lenght = 3, string $exceptMessage = null): void
    {
        // se possuir quantidade de caracteres está menor do que o número mínimo permitido, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (strlen($value) < $lenght) throw new EntityValidationException($exceptMessage ?? "Value must not be smaller than {$lenght} characters");
    }

    // valida se o valor é nulo ou vazio e se possui quantidade de caracteres menor ou igual ao número máximo permitido
    public static function strNullOrMaxLength(mixed $value, int $lenght = 255, string $exceptMessage = null): void
    {
        // se não for vazio ou nulo e possuir quantidade de caracteres maior do que o número máximo permitido, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!empty($value) and strlen($value) > $lenght) throw new EntityValidationException($exceptMessage ?? "Value must not be greater than {$lenght} characters");
    }

    // valida se o valor é nulo ou vazio e se possui quantidade de caracteres maior ou igual ao número mínimo permitido
    public static function strNullOrMinLength(mixed $value, int $lenght = 3, string $exceptMessage = null): void
    {
        // se não for vazio ou nulo e possuir quantidade de caracteres menor do que o número mínimo permitido, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!empty($value) and strlen($value) < $lenght) throw new EntityValidationException($exceptMessage ?? "Value must not be smaller than {$lenght} characters");
    }

    // valida se o valor é compatível com a enumeração CastMemberType
    public static function isCastMemberType(int $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração CastMemberType, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!CastMemberType::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found");
    }
}
