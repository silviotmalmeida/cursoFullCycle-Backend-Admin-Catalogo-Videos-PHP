<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\ValueObject;

// importações
use Core\Domain\ValueObject\Uuid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UuidUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o Uuid
        $uuid  = new Uuid(
            value: '2a4c2c75-b5fb-48af-b00a-8a8251ce4e95'
        );

        // verificando os atributos
        $this->assertSame('2a4c2c75-b5fb-48af-b00a-8a8251ce4e95', (string) $uuid);
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // 
        // validando value
        // 
        // validando value inválido
        try {
            // criando o Uuid
            $uuid  = new Uuid(
                value: 'INVALIDO'
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(InvalidArgumentException::class, $th);
        }

        // validando value válido
        try {
            // criando o Uuid
            $uuid  = new Uuid(
                value: '2a4c2c75-b5fb-48af-b00a-8a8251ce4e95'
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
