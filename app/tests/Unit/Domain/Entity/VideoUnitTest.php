<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\Rating;
use Core\Domain\Exception\EntityValidationException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class VideoUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: true,
            rating: Rating::RATE12,
        );

        // verificando os atributos
        $this->assertNotEmpty($video->id());
        $this->assertSame('New Title', $video->title);
        $this->assertSame('New Description', $video->description);
        $this->assertSame(2024, $video->yearLaunched);
        $this->assertSame(60, $video->duration);
        $this->assertTrue($video->opened);
        $this->assertSame(Rating::RATE12, $video->rating);
        $this->assertNotEmpty($video->createdAt());
        $this->assertNotEmpty($video->updatedAt());
        $this->assertSame($video->createdAt(), $video->updatedAt());
    }

    // função que testa a função de abertura
    public function testOpen()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: false,
            rating: Rating::RATE12,
        );

        // abrindo
        $video->open();

        // verificando
        $this->assertTrue($video->opened);
    }

    // função que testa a função de fechamento
    public function testClose()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: true,
            rating: Rating::RATE12,
        );

        // fechando
        $video->close();

        // verificando
        $this->assertFalse($video->opened);
    }

    // função que testa a função de adicionar/remover categoria
    public function testAddRemoveCategories()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: true,
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addCategory($uuid1);

        // verificando
        $this->assertCount(1, $video->categoriesId);

        // inserindo duplicata
        $video->addCategory($uuid1);

        // verificando
        $this->assertCount(1, $video->categoriesId);

        // inserindo outra
        $video->addCategory($uuid2);

        // verificando
        $this->assertCount(2, $video->categoriesId);

        // removendo não adicionada
        $video->removeCategory($uuid3);

        // verificando
        $this->assertCount(2, $video->categoriesId);

        // removendo
        $video->removeCategory($uuid1);

        // verificando
        $this->assertCount(1, $video->categoriesId);
    }

    // função que testa a função de adicionar/remover genre
    public function testAddRemoveGenres()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: true,
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addGenre($uuid1);

        // verificando
        $this->assertCount(1, $video->genresId);

        // inserindo duplicata
        $video->addGenre($uuid1);

        // verificando
        $this->assertCount(1, $video->genresId);

        // inserindo outra
        $video->addGenre($uuid2);

        // verificando
        $this->assertCount(2, $video->genresId);

        // removendo não adicionada
        $video->removeGenre($uuid3);

        // verificando
        $this->assertCount(2, $video->genresId);

        // removendo
        $video->removeGenre($uuid1);

        // verificando
        $this->assertCount(1, $video->genresId);
    }

    // função que testa a função de adicionar/remover cast member
    public function testAddRemoveCastMembers()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            opened: true,
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addCastMember($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);

        // inserindo duplicata
        $video->addCastMember($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);

        // inserindo outra
        $video->addCastMember($uuid2);

        // verificando
        $this->assertCount(2, $video->castMembersId);

        // removendo não adicionada
        $video->removeCastMember($uuid3);

        // verificando
        $this->assertCount(2, $video->castMembersId);

        // removendo
        $video->removeCastMember($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);
    }

    // // função que testa a função de atualização
    // public function testUpdate()
    // {
    //     // mock de uuid
    //     $uuid = RamseyUuid::uuid4()->toString();
    //     $cat1 = RamseyUuid::uuid4()->toString();
    //     $cat2 = RamseyUuid::uuid4()->toString();
    //     $cat3 = RamseyUuid::uuid4()->toString();

    //     // criando o video
    //     $video = new Video(
    //         id: $uuid,
    //         name: 'name 1',
    //         isActive: true,
    //         categoriesId: [$cat1, $cat2],
    //     );

    //     // retardo na execução para permitir diferenciação do updatedAt
    //     sleep(1);

    //     // atualizando com valores
    //     $video->update(
    //         name: 'name 2',
    //         isActive: false,
    //         categoriesId: [$cat3],
    //     );

    //     // memorizando a data da primeira atualização para comparar com a segunda
    //     $firstUpdateDate = $video->updatedAt();

    //     // verificando os atributos
    //     $this->assertSame($uuid, $video->id());
    //     $this->assertSame('name 2', $video->name);
    //     $this->assertFalse($video->isActive);
    //     $this->assertEquals([$cat3], $video->categoriesId);
    //     $this->assertNotSame($video->createdAt(), $video->updatedAt());

    //     // atualizando sem valores, o updatedAt não deve ser modificado
    //     $video->update();

    //     // verificando os atributos
    //     $this->assertSame($uuid, $video->id());
    //     $this->assertSame('name 2', $video->name);
    //     $this->assertFalse($video->isActive);
    //     $this->assertEquals([$cat3], $video->categoriesId);
    //     $this->assertSame($firstUpdateDate, $video->updatedAt());
    // }

    // // função que testa a função de validação
    // public function testValidate()
    // {
    //     // validando name vazio
    //     try {
    //         // criando o video
    //         $video = new Video(
    //             name: '',
    //         );
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(EntityValidationException::class, $th);
    //     }

    //     // validando name longo
    //     try {
    //         // criando o video
    //         $video = new Video(
    //             name: random_bytes(256),
    //         );
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(EntityValidationException::class, $th);
    //     }

    //     // validando name curto
    //     try {
    //         // criando o video
    //         $video = new Video(
    //             name: random_bytes(2),
    //         );
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(EntityValidationException::class, $th);
    //     }

    //     // validando name válido
    //     try {
    //         // criando o video
    //         $video = new Video(
    //             name: 'name 1',
    //         );
    //     } catch (\Throwable $th) {
    //         // se lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     }
    // }
}
