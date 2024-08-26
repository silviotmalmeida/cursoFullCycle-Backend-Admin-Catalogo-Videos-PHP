<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Validation;

// importações

use Core\Domain\Enum\CastMemberType;
use Core\Domain\Enum\ImageType;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Enum\Rating;
use Core\Domain\Exception\EntityValidationException;

use function PHPUnit\Framework\isNull;

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
    public static function isCastMemberTypeCompatible(int $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração CastMemberType, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!CastMemberType::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found for cast member");
    }

    // valida se o valor é compatível com a enumeração Rating
    public static function isRatingCompatible(string $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração Rating, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!Rating::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found for rating");
    }

    // valida se o valor é compatível com a enumeração MediaStatus
    public static function isMediaStatusCompatible(int $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração MediaStatus, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!MediaStatus::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found for media status");
    }

    // valida se o valor é compatível com a enumeração MediaType
    public static function isMediaTypeCompatible(int $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração MediaType, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!MediaType::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found for media type");
    }

    // valida se o valor é compatível com a enumeração ImageType
    public static function isImageTypeCompatible(int $value, string $exceptMessage = null): void
    {
        // se não for compatível com a enumeração ImageType, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (!ImageType::tryFrom($value)) throw new EntityValidationException($exceptMessage ?? "Type value {$value} not found for image type");
    }

    // valida se um valor não está nulo ou vazio
    public static function notNullOrZero(mixed $value, string $exceptMessage = null): void
    {
        // se for nulo ou vazio, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if ($value === null or $value === 0) throw new EntityValidationException($exceptMessage ?? 'Value must not be null or zero');
    }
}
