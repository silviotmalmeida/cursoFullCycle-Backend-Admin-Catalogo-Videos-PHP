<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Validation;

// importações
use Core\Domain\Exception\EntityValidationException;
use Core\Domain\Validation\DomainValidation;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DomainValidationUnitTest extends TestCase
{
    // função que testa a função de validação notEmpty
    public function testNotEmpty()
    {
        // validando valor vazio e mensagem default
        $value = null;
        // definindo as características da exceção esperada
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('Value should not be empty');
        // executando o método
        DomainValidation::notEmpty($value);

        // validando valor vazio e mensagem customizada
        $value = '';
        $message = 'custom message';
        // definindo as características da exceção esperada
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage($message);
        // executando o método
        DomainValidation::notEmpty($value, $message);

        // validando valor não vazio
        $value = 'value';
        $message = 'custom message';
        // executando o método, não deve disparar exceção
        DomainValidation::notEmpty($value, $message);
    }

    // função que testa a função de validação notNull
    public function testNotNull()
    {
        // validando valor nulo e mensagem default
        $value = null;
        // definindo as características da exceção esperada
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('Value should not be null');
        // executando o método
        DomainValidation::notNull($value);

        // validando valor nulo e mensagem customizada
        $value = null;
        $message = 'custom message';
        // definindo as características da exceção esperada
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage($message);
        // executando o método
        DomainValidation::notNull($value, $message);

        // validando valor não nulo
        $value = 'value';
        $message = 'custom message';
        // executando o método, não deve disparar exceção
        DomainValidation::notNull($value, $message);
    }
}
