<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\ImageType;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Enum\Rating;
use Core\Domain\Exception\EntityValidationException;
use Core\Domain\Notification\NotificationException;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
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
            rating: Rating::RATE12,
        );

        // verificando os atributos
        $this->assertNotEmpty($video->id());
        $this->assertSame('New Title', $video->title);
        $this->assertSame('New Description', $video->description);
        $this->assertSame(2024, $video->yearLaunched);
        $this->assertSame(60, $video->duration);
        $this->assertFalse($video->opened);
        $this->assertSame(Rating::RATE12, $video->rating);
        $this->assertNotEmpty($video->createdAt());
        $this->assertNotEmpty($video->updatedAt());
        $this->assertSame($video->createdAt(), $video->updatedAt());
    }

    // função que testa a função setThumbFile
    public function testSetThumbFile()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        // verificando
        $this->assertNull($video->thumbfile());

        // criando o thumbfile
        $thumbfile = new Image(
            filePath: 'path/thumbfile.png',
            imageType: ImageType::THUMBFILE,
        );
        // setando o thumbfile
        $video->setThumbFile($thumbfile);

        // verificando
        $this->assertSame($thumbfile, $video->thumbfile());
    }

    // função que testa a função setThumbHalf
    public function testSetThumbHalf()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        // verificando
        $this->assertNull($video->thumbHalf());

        // criando o thumbHalf
        $thumbHalf = new Image(
            filePath: 'path/thumbHalf.png',
            imageType: ImageType::THUMBHALF,
        );
        // setando o thumbHalf
        $video->setThumbHalf($thumbHalf);

        // verificando
        $this->assertSame($thumbHalf, $video->thumbHalf());
    }

    // função que testa a função setBannerFile
    public function testSetBannerFile()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        // verificando
        $this->assertNull($video->bannerFile());

        // criando o bannerFile
        $bannerFile = new Image(
            filePath: 'path/bannerFile.png',
            imageType: ImageType::BANNERFILE,
        );
        // setando o bannerFile
        $video->setBannerFile($bannerFile);

        // verificando
        $this->assertSame($bannerFile, $video->bannerFile());
    }

    // função que testa a função setTraileFile
    public function testSetTraileFile()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        // verificando
        $this->assertNull($video->trailerFile());

        // criando o trailerFile
        $trailerFile = new Media(
            filePath: 'path/trailerFile.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::TRAILER,
            encodedPath: ''
        );
        // setando o trailerFile
        $video->setTraileFile($trailerFile);

        // verificando
        $this->assertSame($trailerFile, $video->trailerFile());
    }

    // função que testa a função setVideoFile
    public function testSetVideoFile()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        // verificando
        $this->assertNull($video->videoFile());

        // criando o videoFile
        $videoFile = new Media(
            filePath: 'path/videoFile.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::VIDEO,
            encodedPath: ''
        );
        // setando o videoFile
        $video->setVideoFile($videoFile);

        // verificando
        $this->assertSame($videoFile, $video->videoFile());
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
            rating: Rating::RATE12,
        );
        // abrindo
        $video->open();
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
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addCategoryId($uuid1);

        // verificando
        $this->assertCount(1, $video->categoriesId);

        // inserindo duplicata
        $video->addCategoryId($uuid1);

        // verificando
        $this->assertCount(1, $video->categoriesId);

        // inserindo outra
        $video->addCategoryId($uuid2);

        // verificando
        $this->assertCount(2, $video->categoriesId);

        // removendo não adicionada
        $video->removeCategoryId($uuid3);

        // verificando
        $this->assertCount(2, $video->categoriesId);

        // removendo
        $video->removeCategoryId($uuid1);

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
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addGenreId($uuid1);

        // verificando
        $this->assertCount(1, $video->genresId);

        // inserindo duplicata
        $video->addGenreId($uuid1);

        // verificando
        $this->assertCount(1, $video->genresId);

        // inserindo outra
        $video->addGenreId($uuid2);

        // verificando
        $this->assertCount(2, $video->genresId);

        // removendo não adicionada
        $video->removeGenreId($uuid3);

        // verificando
        $this->assertCount(2, $video->genresId);

        // removendo
        $video->removeGenreId($uuid1);

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
            rating: Rating::RATE12,
        );

        // mock de uuid
        $uuid1 = RamseyUuid::uuid4()->toString();
        $uuid2 = RamseyUuid::uuid4()->toString();
        $uuid3 = RamseyUuid::uuid4()->toString();

        // inserindo
        $video->addCastMemberId($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);

        // inserindo duplicata
        $video->addCastMemberId($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);

        // inserindo outra
        $video->addCastMemberId($uuid2);

        // verificando
        $this->assertCount(2, $video->castMembersId);

        // removendo não adicionada
        $video->removeCastMemberId($uuid3);

        // verificando
        $this->assertCount(2, $video->castMembersId);

        // removendo
        $video->removeCastMemberId($uuid1);

        // verificando
        $this->assertCount(1, $video->castMembersId);
    }

    // função que testa a função de atualização
    public function testUpdate()
    {
        // mock de uuid
        $uuid = RamseyUuid::uuid4()->toString();

        // criando o video
        $video = new Video(
            id: $uuid,
            title: 'Title',
            description: 'Description',
            yearLaunched: 2023,
            duration: 50,
            rating: Rating::RATE12,
        );

        // verificando os atributos
        $this->assertSame($uuid, $video->id());
        $this->assertSame('Title', $video->title);
        $this->assertSame('Description', $video->description);
        $this->assertSame(2023, $video->yearLaunched);
        $this->assertSame(50, $video->duration);
        $this->assertSame(Rating::RATE12, $video->rating);
        $this->assertCount(0, $video->categoriesId);
        $this->assertCount(0, $video->genresId);
        $this->assertCount(0, $video->castMembersId);
        $this->assertSame($video->createdAt(), $video->updatedAt());

        // retardo na execução para permitir diferenciação do updatedAt
        sleep(1);

        // atualizando com valores
        $video->update(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE18,
            categoriesId: [RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString()],
            genresId: [RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString()],
            castMembersId: [RamseyUuid::uuid4()->toString()]
        );

        // verificando os atributos
        $this->assertSame($uuid, $video->id());
        $this->assertSame('New Title', $video->title);
        $this->assertSame('New Description', $video->description);
        $this->assertSame(2024, $video->yearLaunched);
        $this->assertSame(60, $video->duration);
        $this->assertSame(Rating::RATE18, $video->rating);
        $this->assertCount(3, $video->categoriesId);
        $this->assertCount(2, $video->genresId);
        $this->assertCount(1, $video->castMembersId);
        $this->assertNotSame($video->createdAt(), $video->updatedAt());

        // atualizando com valores novamente
        $video->update(
            title: 'New Title 2',
            description: 'New Description 2',
            yearLaunched: 2025,
            duration: 70,
            rating: Rating::RATE10,
            categoriesId: [RamseyUuid::uuid4()->toString()],
            genresId: [RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString()],
            castMembersId: [RamseyUuid::uuid4()->toString(), RamseyUuid::uuid4()->toString()]
        );

        // memorizando a data da segunda atualização para comparar com a terceira
        $secondUpdateDate = $video->updatedAt();

        // verificando os atributos
        $this->assertSame($uuid, $video->id());
        $this->assertSame('New Title 2', $video->title);
        $this->assertSame('New Description 2', $video->description);
        $this->assertSame(2025, $video->yearLaunched);
        $this->assertSame(70, $video->duration);
        $this->assertSame(Rating::RATE10, $video->rating);
        $this->assertCount(1, $video->categoriesId);
        $this->assertCount(3, $video->genresId);
        $this->assertCount(2, $video->castMembersId);
        $this->assertNotSame($video->createdAt(), $video->updatedAt());

        // atualizando sem valores, o updatedAt não deve ser modificado
        $video->update();

        // verificando os atributos
        $this->assertSame($uuid, $video->id());
        $this->assertSame('New Title 2', $video->title);
        $this->assertSame('New Description 2', $video->description);
        $this->assertSame(2025, $video->yearLaunched);
        $this->assertSame(70, $video->duration);
        $this->assertSame(Rating::RATE10, $video->rating);
        $this->assertCount(1, $video->categoriesId);
        $this->assertCount(3, $video->genresId);
        $this->assertCount(2, $video->castMembersId);
        $this->assertSame($secondUpdateDate, $video->updatedAt());
    }

    // função que testa a função de validação de title válido
    public function testValidateTitleValid()
    {
        $video = new Video(
            title: str_repeat('a', random_int(3, 255)),
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        $this->assertInstanceOf(Video::class, $video);
    }

    // função que testa a função de validação de title vazio
    public function testValidateTitleEmpty()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: '',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de title longo
    public function testValidateTitleLong()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: str_repeat('a', 256),
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de title curto
    public function testValidateTitleShort()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: str_repeat('a', 2),
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de description válido
    public function testValidateDescriptionValid()
    {
        $video = new Video(
            title: 'New Title',
            description: str_repeat('a', random_int(3, 255)),
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        $this->assertInstanceOf(Video::class, $video);
    }

    // função que testa a função de validação de description vazio
    public function testValidateDescriptionEmpty()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: 'New Title',
            description: '',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de description longo
    public function testValidateDescriptionLong()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: 'New Title',
            description: str_repeat('a', 256),
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de description curto
    public function testValidateDescriptionShort()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: 'New Title',
            description: str_repeat('a', 2),
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de yearLaunched válido
    public function testValidateYearLaunchedValid()
    {
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: random_int(1, 9999),
            duration: 60,
            rating: Rating::RATE12,
        );
        $this->assertInstanceOf(Video::class, $video);
    }

    // função que testa a função de validação de yearLaunched zerado
    public function testValidateYearLaunchedZero()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 0,
            duration: 60,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de duration válido
    public function testValidateDurationValid()
    {
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        $this->assertInstanceOf(Video::class, $video);
    }

    // função que testa a função de validação de duration zerado
    public function testValidateDurationZero()
    {
        $this->expectException(NotificationException::class);
        new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 0,
            rating: Rating::RATE12,
        );
    }

    // função que testa a função de validação de rating válido
    public function testValidateRatingValid()
    {
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        $this->assertInstanceOf(Video::class, $video);
    }

    // função que testa a função de validação de rating inválido
    public function testValidateRatingInvalid()
    {
        $this->expectException(EntityValidationException::class);
        new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: 'INVALIDO',
        );
    }
}
