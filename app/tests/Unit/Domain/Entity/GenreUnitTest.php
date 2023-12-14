<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Exception\EntityValidationException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class GenreUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o genre
        $genre = new Genre(
            name: 'New Genre',
            isActive: true
        );

        // verificando os atributos
        $this->assertNotEmpty($genre->id());
        $this->assertSame('New Genre', $genre->name);
        $this->assertTrue($genre->isActive);
        $this->assertNotEmpty($genre->createdAt());
        $this->assertNotEmpty($genre->updatedAt());
        $this->assertSame($genre->createdAt(), $genre->updatedAt());
    }

    // função que testa a função de ativação
    public function testActivate()
    {
        // criando o genre
        $genre = new Genre(
            name: 'New Genre',
            isActive: false
        );

        // ativando
        $genre->activate();

        // verificando
        $this->assertTrue($genre->isActive);
    }

    // função que testa a função de desativação
    public function testDeactivate()
    {
        // criando o genre
        $genre = new Genre(
            name: 'New Genre',
            isActive: true
        );

        // desativando
        $genre->deactivate();

        // verificando
        $this->assertFalse($genre->isActive);
    }

    // função que testa a função de adicionar/remover categoria
    public function testAddRemoveCategories()
    {
        // criando o genre
        $genre = new Genre(
            name: 'New Genre',
            isActive: false
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $genre->addCategory($uuid1);

        // verificando
        $this->assertCount(1, $genre->categoriesId);

        // inserindo duplicata
        $genre->addCategory($uuid1);

        // verificando
        $this->assertCount(1, $genre->categoriesId);

        // inserindo outra
        $genre->addCategory($uuid2);

        // verificando
        $this->assertCount(2, $genre->categoriesId);

        // removendo não adicionada
        $genre->removeCategory($uuid3);

        // verificando
        $this->assertCount(2, $genre->categoriesId);

        // removendo
        $genre->removeCategory($uuid1);

        // verificando
        $this->assertCount(1, $genre->categoriesId);
    }

    // função que testa a função de atualização
    public function testUpdate()
    {
        // mock de uuid
        $uuid = RamseyUuid::uuid4()->toString();

        // criando o genre
        $genre = new Genre(
            id: $uuid,
            name: 'name 1',
            isActive: true
        );

        // retardo na execução para permitir diferenciação do updatedAt
        sleep(1);

        // atualizando com valores
        $genre->update(
            name: 'name 2',
            isActive: false,
        );

        // memorizando a data da primeira atualização para comparar com a segunda
        $firstUpdateDate = $genre->updatedAt();

        // verificando os atributos
        $this->assertSame($uuid, $genre->id());
        $this->assertSame('name 2', $genre->name);
        $this->assertFalse($genre->isActive);
        $this->assertNotSame($genre->createdAt(), $genre->updatedAt());

        // atualizando sem valores, o updatedAt não deve ser modificado
        $genre->update();

        // verificando os atributos
        $this->assertSame($uuid, $genre->id());
        $this->assertSame('name 2', $genre->name);
        $this->assertFalse($genre->isActive);
        $this->assertSame($firstUpdateDate, $genre->updatedAt());
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // validando name vazio
        try {
            // criando o genre
            $genre = new Genre(
                name: '',
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name longo
        try {
            // criando o genre
            $genre = new Genre(
                name: random_bytes(256),
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name curto
        try {
            // criando o genre
            $genre = new Genre(
                name: random_bytes(2),
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name válido
        try {
            // criando o genre
            $genre = new Genre(
                name: 'name 1',
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
