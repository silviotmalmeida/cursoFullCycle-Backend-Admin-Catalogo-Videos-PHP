<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\PaginationInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoInputDto;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoOutputDto;
use Core\UseCase\Video\Paginate\PaginateVideoUseCase;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MocksFactory;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class PaginateVideoUseCaseUnitTest extends TestCase
{

    // função que testa o método de execução retornando lista vazia
    public function testExecuteReturningEmptyList()
    {
        // definindo os atributos a serem utilizados nos mocks
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $perPage = 10;
        $items = [];
        $total = 0;
        $lastPage = 1;
        $firstPage = 1;
        $currentPage = $startPage;
        $perPage = $perPage;
        $to = 1;
        $from = 1;

        // criando o inputDto
        $inputDto = new PaginateVideoInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o mock do Pagination
        $mockPagination = Mockery::mock(PaginationInterface::class);
        $mockPagination->shouldReceive('items')->andReturn($items); //definindo o retorno do items()
        $mockPagination->shouldReceive('total')->andReturn($total); //definindo o retorno do total()
        $mockPagination->shouldReceive('lastPage')->andReturn($lastPage); //definindo o retorno do lastPage()
        $mockPagination->shouldReceive('firstPage')->andReturn($firstPage); //definindo o retorno do firstPage()
        $mockPagination->shouldReceive('currentPage')->andReturn($currentPage); //definindo o retorno do currentPage()
        $mockPagination->shouldReceive('perPage')->andReturn($perPage); //definindo o retorno do perPage()
        $mockPagination->shouldReceive('to')->andReturn($to); //definindo o retorno do to()
        $mockPagination->shouldReceive('from')->andReturn($from); //definindo o retorno do from()

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('paginate')->times(1)->with($filter, $order, $startPage, $perPage)->andReturn($mockPagination); //definindo o retorno do paginate()

        // criando o usecase
        $useCase = new PaginateVideoUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateVideoOutputDto::class, $responseUseCase);
        $this->assertCount(0, $responseUseCase->items);
        $this->assertSame($items, $responseUseCase->items);
        $this->assertSame($total, $responseUseCase->total);
        $this->assertSame($lastPage, $responseUseCase->last_page);
        $this->assertSame($firstPage, $responseUseCase->first_page);
        $this->assertSame($currentPage, $responseUseCase->current_page);
        $this->assertSame($perPage, $responseUseCase->per_page);
        $this->assertSame($to, $responseUseCase->to);
        $this->assertSame($from, $responseUseCase->from);
    }

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
    public function testExecuteReturningExistingList(
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

        // definindo os atributos a serem utilizados nos mocks
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $perPage = 10;
        $items = [$initialVideo, $initialVideo];
        $total = 2;
        $lastPage = 1;
        $firstPage = 1;
        $currentPage = $startPage;
        $perPage = $perPage;
        $to = 1;
        $from = 1;

        // criando o inputDto
        $inputDto = new PaginateVideoInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o mock do Pagination
        $mockPagination = Mockery::mock(PaginationInterface::class);
        $mockPagination->shouldReceive('items')->andReturn($items); //definindo o retorno do items()
        $mockPagination->shouldReceive('total')->andReturn($total); //definindo o retorno do total()
        $mockPagination->shouldReceive('lastPage')->andReturn($lastPage); //definindo o retorno do lastPage()
        $mockPagination->shouldReceive('firstPage')->andReturn($firstPage); //definindo o retorno do firstPage()
        $mockPagination->shouldReceive('currentPage')->andReturn($currentPage); //definindo o retorno do currentPage()
        $mockPagination->shouldReceive('perPage')->andReturn($perPage); //definindo o retorno do perPage()
        $mockPagination->shouldReceive('to')->andReturn($to); //definindo o retorno do to()
        $mockPagination->shouldReceive('from')->andReturn($from); //definindo o retorno do from()

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('paginate')->times(1)->with($filter, $order, $startPage, $perPage)->andReturn($mockPagination); //definindo o retorno do paginate()
        
        // criando o usecase
        $useCase = new PaginateVideoUseCase(
            $mockRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateVideoOutputDto::class, $responseUseCase);
        $this->assertCount(2, $responseUseCase->items);
        $this->assertSame($items, $responseUseCase->items);
        $this->assertSame($total, $responseUseCase->total);
        $this->assertSame($lastPage, $responseUseCase->last_page);
        $this->assertSame($firstPage, $responseUseCase->first_page);
        $this->assertSame($currentPage, $responseUseCase->current_page);
        $this->assertSame($perPage, $responseUseCase->per_page);
        $this->assertSame($to, $responseUseCase->to);
        $this->assertSame($from, $responseUseCase->from);
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
