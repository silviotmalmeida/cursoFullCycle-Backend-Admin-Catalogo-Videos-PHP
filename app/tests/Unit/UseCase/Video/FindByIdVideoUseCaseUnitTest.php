<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoInputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoOutputDto;
use Core\UseCase\Video\FindById\FindByIdVideoUseCase;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MocksFactory;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class FindByIdVideoUseCaseUnitTest extends TestCase
{

    // provedor de dados do testExecute
    public function dataProviderExecute(): array
    {
        return [
            ['', '', '', '', ''],
            ['path_thumbFile', '', '', '', ''],
            ['', 'path_thumbHalf', '', '', ''],
            ['', '', 'path_bannerFile', '', ''],
            ['', '', '', 'path_trailerFile', ''],
            ['', '', '', '', 'path_videoFile'],
            ['path_thumbFile', 'path_thumbHalf', 'path_bannerFile', 'path_trailerFile', 'path_videoFile'],
        ];
    }
    // função que testa o método de execução, com sucesso
    // utiliza o dataProvider dataProviderExecute
    /**
     * @dataProvider dataProviderExecute
     */
    public function testExecute(
        string $pathThumbFile,
        string $pathThumbHalf,
        string $pathBannerFile,
        string $pathTrailerFile,
        string $pathVideoFile
    ) {
        // criando o video inicial
        $initialVideo = self::createInitialVideo(
            $pathThumbFile,
            $pathThumbHalf,
            $pathBannerFile,
            $pathTrailerFile,
            $pathVideoFile
        );

        // criando o inputDto
        $inputDto = new FindByIdVideoInputDto($initialVideo->id());

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($initialVideo->id())->andReturn($initialVideo); //definindo o retorno do findById()
        
        // criando o usecase
        $useCase = new FindByIdVideoUseCase(
            $mockRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdVideoOutputDto::class, $responseUseCase);
        $this->assertSame($initialVideo->id(), $responseUseCase->id);
        $this->assertSame($initialVideo->title, $responseUseCase->title);
        $this->assertSame($initialVideo->description, $responseUseCase->description);
        $this->assertSame($initialVideo->yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($initialVideo->duration, $responseUseCase->duration);
        $this->assertSame($initialVideo->opened, $responseUseCase->opened);
        $this->assertSame($initialVideo->rating, $responseUseCase->rating);
        if ($pathThumbFile) $this->assertSame($initialVideo->thumbFile->filePath(), $responseUseCase->thumbFile);
        if ($pathThumbHalf) $this->assertSame($initialVideo->thumbHalf->filePath(), $responseUseCase->thumbHalf);
        if ($pathBannerFile) $this->assertSame($initialVideo->bannerFile->filePath(), $responseUseCase->bannerFile);
        if ($pathTrailerFile) $this->assertSame($initialVideo->trailerFile->filePath(), $responseUseCase->trailerFile);
        if ($pathVideoFile) $this->assertSame($initialVideo->videoFile->filePath(), $responseUseCase->videoFile);
        $this->assertEquals($initialVideo->categoriesId, $responseUseCase->categoriesId);
        $this->assertEquals($initialVideo->genresId, $responseUseCase->genresId);
        $this->assertEquals($initialVideo->castMembersId, $responseUseCase->castMembersId);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
    }

    // função auxiliar para criação do video inicial
    private static function createInitialVideo(
        string $thumbFile,
        string $thumbHalf,
        string $bannerFile,
        string $trailerFile,
        string $videoFile,
    ): Video {
        $inputDto = new InsertVideoInputDto(
            title: 'Original Title',
            description: 'Original Description',
            yearLaunched: 2023,
            duration: 50,
            rating: Rating::RATE10,
            opened: false,
            categoriesId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
            genresId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
            castMembersId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
        );

        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);
        if ($thumbFile) $videoBuilder->addThumbFile($thumbFile);
        if ($thumbHalf) $videoBuilder->addThumbHalf($thumbHalf);
        if ($bannerFile) $videoBuilder->addBannerFile($bannerFile);
        if ($trailerFile) $videoBuilder->addTrailerFile($trailerFile, MediaStatus::PENDING);
        if ($videoFile) $videoBuilder->addVideoFile($videoFile, MediaStatus::PENDING);

        return $videoBuilder->getEntity();
    }

    // método para encerrar os mocks
    protected function tearDown(): void
    {
        // encerrando os mocks
        Mockery::close();

        parent::tearDown();
    }
}
