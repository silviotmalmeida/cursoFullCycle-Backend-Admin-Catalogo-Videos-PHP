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
    // função que testa a função de validação NotNullOrEmpty
    public function testNotNullOrEmpty()
    {
        // validando valor nulo e mensagem default
        try {
            $value = null;
            // executando o método
            DomainValidation::notNullOrEmpty($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be null or empty', $th->getMessage());
        }

        // validando valor vazio e mensagem default
        try {
            $value = '';
            // executando o método
            DomainValidation::notNullOrEmpty($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be null or empty', $th->getMessage());
        }

        // validando valor nulo e mensagem customizada
        try {
            $value = null;
            $message = 'custom message';
            // executando o método
            DomainValidation::notNullOrEmpty($value, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor vazio e mensagem customizada
        try {
            $value = '';
            $message = 'custom message';
            // executando o método
            DomainValidation::notNullOrEmpty($value, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor não nulo
        try {
            $value = 'value';
            $message = 'custom message';
            // executando o método
            DomainValidation::notNullOrEmpty($value, $message);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }

    // função que testa a função de validação strMaxLenght
    public function testStrMaxLenght()
    {
        // validando valor maior default e mensagem default
        try {
            $value = random_bytes(256);
            // executando o método
            DomainValidation::strMaxLenght($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be greater than 255 characters', $th->getMessage());
        }

        // validando valor maior customizado e mensagem default
        try {
            $value = random_bytes(51);
            $lenght = 50;
            // executando o método
            DomainValidation::strMaxLenght($value, $lenght);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals("Value must not be greater than {$lenght} characters", $th->getMessage());
        }

        // validando valor maior customizado e mensagem customizada
        try {
            $value = random_bytes(51);
            $lenght = 50;
            $message = 'custom message';
            // executando o método
            DomainValidation::strMaxLenght($value, $lenght, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor válido default
        try {
            $value = random_bytes(255);
            // executando o método
            DomainValidation::strMaxLenght($value);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor válido customizado
        try {
            $value = random_bytes(50);
            $lenght = 50;
            // executando o método
            DomainValidation::strMaxLenght($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }

    // função que testa a função de validação strMinLenght
    public function testStrMinLenght()
    {
        // validando valor menor default e mensagem default
        try {
            $value = random_bytes(2);
            // executando o método
            DomainValidation::strMinLenght($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be smaller than 3 characters', $th->getMessage());
        }

        // validando valor menor customizado e mensagem default
        try {
            $value = random_bytes(4);
            $lenght = 5;
            // executando o método
            DomainValidation::strMinLenght($value, $lenght);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals("Value must not be smaller than {$lenght} characters", $th->getMessage());
        }

        // validando valor menor customizado e mensagem customizada
        try {
            $value = random_bytes(4);
            $lenght = 5;
            $message = 'custom message';
            // executando o método
            DomainValidation::strMinLenght($value, $lenght, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor válido default
        try {
            $value = random_bytes(3);
            // executando o método
            DomainValidation::strMinLenght($value);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor válido customizado
        try {
            $value = random_bytes(5);
            $lenght = 5;
            // executando o método
            DomainValidation::strMinLenght($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }

    // função que testa a função de validação strNullOrMaxLength
    public function testStrNullOrMaxLength()
    {
        // validando valor maior default e mensagem default
        try {
            $value = random_bytes(256);
            // executando o método
            DomainValidation::strNullOrMaxLength($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be greater than 255 characters', $th->getMessage());
        }

        // validando valor maior customizado e mensagem default
        try {
            $value = random_bytes(51);
            $lenght = 50;
            // executando o método
            DomainValidation::strNullOrMaxLength($value, $lenght);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals("Value must not be greater than {$lenght} characters", $th->getMessage());
        }

        // validando valor maior customizado e mensagem customizada
        try {
            $value = random_bytes(51);
            $lenght = 50;
            $message = 'custom message';
            // executando o método
            DomainValidation::strNullOrMaxLength($value, $lenght, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor válido default
        try {
            $value = random_bytes(255);
            // executando o método
            DomainValidation::strNullOrMaxLength($value);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor válido customizado
        try {
            $value = random_bytes(50);
            $lenght = 50;
            // executando o método
            DomainValidation::strNullOrMaxLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor nulo
        try {
            $value = null;
            $lenght = 50;
            // executando o método
            DomainValidation::strNullOrMaxLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor vazio
        try {
            $value = '';
            $lenght = 50;
            // executando o método
            DomainValidation::strNullOrMaxLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }

    // função que testa a função de validação strNullOrMixLength
    public function testStrNullOrMixLength()
    {
        // validando valor menor default e mensagem default
        try {
            $value = random_bytes(2);
            // executando o método
            DomainValidation::strNullOrMixLength($value);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals('Value must not be smaller than 3 characters', $th->getMessage());
        }

        // validando valor menor customizado e mensagem default
        try {
            $value = random_bytes(4);
            $lenght = 5;
            // executando o método
            DomainValidation::strNullOrMixLength($value, $lenght);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals("Value must not be smaller than {$lenght} characters", $th->getMessage());
        }

        // validando valor menor customizado e mensagem customizada
        try {
            $value = random_bytes(4);
            $lenght = 5;
            $message = 'custom message';
            // executando o método
            DomainValidation::strNullOrMixLength($value, $lenght, $message);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando os dados da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
            $this->assertEquals($message, $th->getMessage());
        }

        // validando valor válido default
        try {
            $value = random_bytes(3);
            // executando o método
            DomainValidation::strNullOrMixLength($value);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor válido customizado
        try {
            $value = random_bytes(5);
            $lenght = 5;
            // executando o método
            DomainValidation::strNullOrMixLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor nulo
        try {
            $value = null;
            $lenght = 5;
            // executando o método
            DomainValidation::strNullOrMixLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando valor vazio
        try {
            $value = '';
            $lenght = 5;
            // executando o método
            DomainValidation::strNullOrMixLength($value, $lenght);
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
