<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\ValueObject;

// importações

use Core\Domain\Enum\ImageType;
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
            filePath: 'path/para/image.png',
            imageType: ImageType::THUMB,
        );
        
        // verificando os atributos
        $this->assertSame('path/para/image.png', $image->filePath());
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // 
        // validando filePath
        // 
        // validando filePath vazio
        try {
            // criando o Image
            $image  = new Image(
                filePath: '',
                imageType: ImageType::THUMB,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando filePath válido
        try {
            // criando o Image
            $image  = new Image(
                filePath: 'path/para/image.png',
                imageType: ImageType::THUMB,
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // 
        // validando imageType
        //
        // validando imageType inválido
        try {
            // criando o Image
            $image  = new Image(
                filePath: 'path/para/image.png',
                imageType: 0,
            );
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando imageType válido
        try {
            // criando o Image
            $image  = new Image(
                filePath: 'path/para/image.png',
                imageType: ImageType::THUMB,
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
