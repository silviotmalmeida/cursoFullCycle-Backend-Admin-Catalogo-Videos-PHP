<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\ValueObject;

// importações
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Exception\EntityValidationException;
use Core\Domain\ValueObject\Media;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class MediaUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o media
        $media = new Media(
            filePath: 'path/media.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::TRAILER,
            encodedPath: ''
        );

        // verificando os atributos
        $this->assertSame('path/media.mp4', $media->filePath());
        $this->assertSame(MediaStatus::PENDING, $media->mediaStatus());
        $this->assertSame(MediaType::TRAILER, $media->mediaType());
        $this->assertSame('', $media->encodedPath());
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // 
        // validando filePath
        // 
        // validando filePath vazio
        try {
            // criando o Media
            $media = new Media(
                filePath: '',
                mediaStatus: MediaStatus::PENDING,
                mediaType: MediaType::TRAILER,
                encodedPath: ''
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando filePath válido
        try {
            // criando o Media
            $media = new Media(
                filePath: 'path/media.mp4',
                mediaStatus: MediaStatus::PENDING,
                mediaType: MediaType::TRAILER,
                encodedPath: ''
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // 
        // validando mediaStatus
        //
        // validando mediaStatus inválido
        try {
            // criando o Media
            $media = new Media(
                filePath: 'path/media.mp4',
                mediaStatus: 0,
                mediaType: MediaType::TRAILER,
                encodedPath: ''
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção            
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando mediaStatus válido
        try {
            // criando o Media
            $media = new Media(
                filePath: 'path/media.mp4',
                mediaStatus: MediaStatus::PENDING,
                mediaType: MediaType::TRAILER,
                encodedPath: ''
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // 
        // validando mediaType
        //
        // validando mediaType inválido
        try {
            // criando o Media
            $media = new Media(
                filePath: 'path/media.mp4',
                mediaStatus: MediaStatus::PENDING,
                mediaType: 0,
                encodedPath: ''
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção            
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando mediaType válido
        try {
            // criando o Media
            $media = new Media(
                filePath: 'path/media.mp4',
                mediaStatus: MediaStatus::PENDING,
                mediaType: MediaType::TRAILER,
                encodedPath: ''
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
