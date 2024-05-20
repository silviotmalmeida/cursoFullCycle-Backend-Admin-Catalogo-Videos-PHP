<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\ValueObject;

// importações
use Core\Domain\Exception\EntityValidationException;
use Core\Domain\ValueObject\Image;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class ImageUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o Image
        $image  = new Image(
            path: 'path/para/image.png'
        );
        
        // verificando os atributos
        $this->assertSame('path/para/image.png', $image->path());
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // 
        // validando path
        // 
        // validando path vazio
        try {
            // criando o Image
            $image  = new Image(
                path: ''
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando path válido
        try {
            // criando o Image
            $image  = new Image(
                path: 'path/para/image.png'
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
