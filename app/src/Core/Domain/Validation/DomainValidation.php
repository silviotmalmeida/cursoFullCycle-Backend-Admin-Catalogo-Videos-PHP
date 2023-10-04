<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Validation;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Exception\EntityValidationException;

use function PHPUnit\Framework\isNull;

// definindo a classe
// classe com  métodos genéricos de validação
class DomainValidation
{

    // valida se um valor não está vazio
    public static function notEmpty(mixed $value, string $exceptMessage = null)
    {
        // se for vazio, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (empty($value)) throw new EntityValidationException($exceptMessage ?? 'Value should not be empty');
    }

    // valida se um valor não está nulo
    public static function notNull(mixed $value, string $exceptMessage = null)
    {
        // se for nulo, lança exceção
        // envia a mensagem recebida caso seja válida, senão envia mensagem padrão
        if (isNull($value)) throw new EntityValidationException($exceptMessage ?? 'Value should not be null');
    }
}
