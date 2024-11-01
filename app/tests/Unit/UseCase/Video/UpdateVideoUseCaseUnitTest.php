<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Entity\Video;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Enum\Rating;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\DTO\UpdateVideoOutputDto;
use Core\UseCase\Video\Update\UpdateVideoUseCase;
use DateTime;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MocksFactory;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateVideoUseCaseUnitTest extends TestCase
{

    // provedor de dados do testExecute
    public function dataProviderExecute(): array
    {
        $arrayFile = ['tmp' => 'tmp/filename'];
        $filepath = 'storage/filename';

        return [
            [[], [], [], [], [], 0, $filepath, 0, '', '', '', '', ''],
            [$arrayFile, [], [], [], [], 1, $filepath, 0, $filepath, '', '', '', ''],
            [[], $arrayFile, [], [], [], 1, $filepath, 0, '', $filepath, '', '', ''],
            [[], [], $arrayFile, [], [], 1, $filepath, 0, '', '', $filepath, '', ''],
            [[], [], [], $arrayFile, [], 1, $filepath, 0, '', '', '', $filepath, ''],
            [[], [], [], [], $arrayFile, 1, $filepath, 1, '', '', '', '', $filepath],
            [$arrayFile, $arrayFile, $arrayFile, $arrayFile, $arrayFile, 5, $filepath, 1, $filepath, $filepath, $filepath, $filepath, $filepath],
        ];
    }
    // função que testa o método de execução, com sucesso
    // utiliza o dataProvider dataProviderExecute
    /**
     * @dataProvider dataProviderExecute
     */
    public function testExecute(
        array $thumbFile,
        array $thumbHalf,
        array $bannerFile,
        array $trailerFile,
        array $videoFile,
        int $qtdStoreTimes,
        string $returnStorage,
        int $qtdDispatchTimes,
        string $pathThumbFile,
        string $pathThumbHalf,
        string $pathBannerFile,
        string $pathTrailerFile,
        string $pathVideoFile
    ) {
        // definindo os atributos a serem utilizados nos mocks
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $opened = false;
        $rating = Rating::RATE10;
        $nameCategory = 'Category Name';
        $descriptionCategory = 'Category Description';
        $isActiveCategory = true;
        $nameGenre = 'Genre Name';
        $isActiveGenre = true;
        $nameCastMember = 'Cast Member Name';
        $typeCastMember = CastMemberType::ACTOR;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $genreId1 = Uuid::uuid4()->toString();
        $castMemberId1 = Uuid::uuid4()->toString();
        $castMemberId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $genresId = [$genreId1];
        $castMembersId = [$castMemberId1, $castMemberId2];

        // criando o mock da categoria 1
        $mockCategory1 = MocksFactory::createCategoryMock($categoryId1, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock da categoria 2
        $mockCategory2 = MocksFactory::createCategoryMock($categoryId2, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock do genre 1
        $mockGenre1 = MocksFactory::createGenreMock($genreId1, $nameGenre, $isActiveGenre, $categoriesId);

        // criando o mock do cast member 1
        $mockCastMember1 = MocksFactory::createCastMemberMock($castMemberId1, $nameCastMember, $typeCastMember);

        // criando o mock do cast member 2
        $mockCastMember2 = MocksFactory::createCastMemberMock($castMemberId2, $nameCastMember, $typeCastMember);

        // criando o video inicial
        $initialVideo = self::createInitialVideo();
        sleep(1);

        // criando o input para o builder
        $input =  (object) array(
            'id' => $initialVideo->id(),
            'title' => $title,
            'description' => $description,
            'yearLaunched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
            'categoriesId' => $categoriesId,
            'genresId' => $genresId,
            'castMembersId' => $castMembersId,
            'createdAt' => $initialVideo->createdAt(),
            'updatedAt' => (new DateTime())->format('Y-m-d H:i:s'),
        );

        // criando o inputDto
        $inputDto = self::createUpdateVideoInputDto(
            $initialVideo->id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating,
            $opened,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile,
        );

        // criando a entidade com os dados do input
        $videoBuilder = (new CreateVideoBuilder)->createEntity($input);
        $entity = $videoBuilder->getEntity();

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->andReturn($initialVideo); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($entity); //definindo o retorno do update()
        $mockRepository->shouldReceive('updateMedia')->times(1)->andReturn($entity); //definindo o retorno do updateMedia()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(1)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(0)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times($qtdStoreTimes)->andReturn($returnStorage); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times($qtdDispatchTimes)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockGenre1]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $mockRepository,
            $mockTransactionDb,
            $mockFileStorage,
            $mockEventManager,
            $mockCategoryRepository,
            $mockGenreRepository,
            $mockcastMemberRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($initialVideo->id(), $responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($opened, $responseUseCase->opened);
        $this->assertSame($rating, $responseUseCase->rating);
        if ($pathThumbFile) $this->assertSame($returnStorage, $responseUseCase->thumbFile);
        if ($pathThumbHalf) $this->assertSame($returnStorage, $responseUseCase->thumbHalf);
        if ($pathBannerFile) $this->assertSame($returnStorage, $responseUseCase->bannerFile);
        if ($pathTrailerFile) $this->assertSame($returnStorage, $responseUseCase->trailerFile);
        if ($pathVideoFile) $this->assertSame($returnStorage, $responseUseCase->videoFile);
        $this->assertEquals($categoriesId, $responseUseCase->categoriesId);
        $this->assertEquals($genresId, $responseUseCase->genresId);
        $this->assertEquals($castMembersId, $responseUseCase->castMembersId);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);
    }

    // função que testa o método de execução, sem sucesso e com rollback
    public function testExecuteRollback()
    {
        // definindo os atributos a serem utilizados nos mocks
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $opened = false;
        $rating = Rating::RATE10;
        $nameCategory = 'Category Name';
        $descriptionCategory = 'Category Description';
        $isActiveCategory = true;
        $nameGenre = 'Genre Name';
        $isActiveGenre = true;
        $nameCastMember = 'Cast Member Name';
        $typeCastMember = CastMemberType::ACTOR;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $genreId1 = Uuid::uuid4()->toString();
        $castMemberId1 = Uuid::uuid4()->toString();
        $castMemberId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $genresId = [$genreId1];
        $castMembersId = [$castMemberId1, $castMemberId2];
        $thumbFile = ['tmp' => 'tmp/thumbFile.png'];
        $thumbHalf = ['tmp' => 'tmp/thumbHalf.png'];
        $bannerFile = ['tmp' => 'tmp/bannerFile.png'];
        $trailerFile = ['tmp' => 'tmp/trailerFile.mp4'];
        $videoFile = ['tmp' => 'tmp/videoFile.mp4'];

        // criando o mock da categoria 1
        $mockCategory1 = MocksFactory::createCategoryMock($categoryId1, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock da categoria 2
        $mockCategory2 = MocksFactory::createCategoryMock($categoryId2, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock do genre 1
        $mockGenre1 = MocksFactory::createGenreMock($genreId1, $nameGenre, $isActiveGenre, $categoriesId);

        // criando o mock do cast member 1
        $mockCastMember1 = MocksFactory::createCastMemberMock($castMemberId1, $nameCastMember, $typeCastMember);

        // criando o mock do cast member 2
        $mockCastMember2 = MocksFactory::createCastMemberMock($castMemberId2, $nameCastMember, $typeCastMember);

        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = self::createUpdateVideoInputDto(
            $initialVideo->id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating,
            $opened,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile
        );

        // criando a entidade com os dados do input
        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);
        $entity = $videoBuilder->getEntity();

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->andReturn($initialVideo); //definindo o retorno do insert()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($entity); //definindo o retorno do insert()
        $mockRepository->shouldReceive('updateMedia')->times(1)->andReturn($entity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(5)->andReturn('path_do_storage'); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(5)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times(1)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockGenre1]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("rollback test");

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $mockRepository,
            $mockTransactionDb,
            $mockFileStorage,
            $mockEventManager,
            $mockCategoryRepository,
            $mockGenreRepository,
            $mockcastMemberRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto, true);
    }

    // provedor de dados do testExecuteCategoriesValidationFail
    public function dataProviderExecuteCategoriesValidationFail(): array
    {
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $nameCategory = 'Category Name';
        $descriptionCategory = 'Category Description';
        $isActiveCategory = true;
        $mockCategory1 = MocksFactory::createCategoryMock($categoryId1, $nameCategory, $descriptionCategory, $isActiveCategory);
        $mockCategory2 = MocksFactory::createCategoryMock($categoryId2, $nameCategory, $descriptionCategory, $isActiveCategory);

        return [
            [$categoryId1, $categoryId2, [$mockCategory1], "Category $categoryId2 not found"],
            [$categoryId1, $categoryId2, [], "Categories $categoryId1, $categoryId2 not found"]
        ];
    }
    // função que testa o método de execução, sem sucesso na validação de categorias
    // utiliza o dataProvider dataProviderExecuteCategoriesValidationFail
    /**
     * @dataProvider dataProviderExecuteCategoriesValidationFail
     */
    public function testExecuteCategoriesValidationFail(string $categoryId1, string $categoryId2, array $returnCategoryRepository, string $exceptionMessage)
    {
        // definindo os atributos a serem utilizados nos mocks
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $opened = false;
        $rating = Rating::RATE10;
        $nameGenre = 'Genre Name';
        $isActiveGenre = true;
        $nameCastMember = 'Cast Member Name';
        $typeCastMember = CastMemberType::ACTOR;
        $genreId1 = Uuid::uuid4()->toString();
        $castMemberId1 = Uuid::uuid4()->toString();
        $castMemberId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $genresId = [$genreId1];
        $castMembersId = [$castMemberId1, $castMemberId2];
        $thumbFile = ['tmp' => 'tmp/thumbFile.png'];
        $thumbHalf = ['tmp' => 'tmp/thumbHalf.png'];
        $bannerFile = ['tmp' => 'tmp/bannerFile.png'];
        $trailerFile = ['tmp' => 'tmp/trailerFile.mp4'];
        $videoFile = ['tmp' => 'tmp/videoFile.mp4'];

        // criando o mock do genre 1
        $mockGenre1 = MocksFactory::createGenreMock($genreId1, $nameGenre, $isActiveGenre, $categoriesId);

        // criando o mock do cast member 1
        $mockCastMember1 = MocksFactory::createCastMemberMock($castMemberId1, $nameCastMember, $typeCastMember);

        // criando o mock do cast member 2
        $mockCastMember2 = MocksFactory::createCastMemberMock($castMemberId2, $nameCastMember, $typeCastMember);

        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = self::createUpdateVideoInputDto(
            $initialVideo->id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating,
            $opened,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile
        );

        // criando a entidade com os dados do input
        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);
        $entity = $videoBuilder->getEntity();

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(0)->andReturn($initialVideo); //definindo o retorno do insert()
        $mockRepository->shouldReceive('update')->times(0)->andReturn($entity); //definindo o retorno do insert()
        $mockRepository->shouldReceive('updateMedia')->times(0)->andReturn($entity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(0)->andReturn('path_do_storage'); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times(0)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn($returnCategoryRepository); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockGenre1]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $mockRepository,
            $mockTransactionDb,
            $mockFileStorage,
            $mockEventManager,
            $mockCategoryRepository,
            $mockGenreRepository,
            $mockcastMemberRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);
    }

    // provedor de dados do testExecuteGenreValidationFail
    public function dataProviderExecuteGenreValidationFail(): array
    {
        $genreId1 = Uuid::uuid4()->toString();
        $genreId2 = Uuid::uuid4()->toString();
        $nameGenre = 'Genre Name';
        $isActiveGenre = true;
        $mockGenre1 = MocksFactory::createGenreMock($genreId1, $nameGenre, $isActiveGenre, []);
        $mockGenre2 = MocksFactory::createGenreMock($genreId2, $nameGenre, $isActiveGenre, []);

        return [
            [$genreId1, $genreId2, [$mockGenre1], "Genre $genreId2 not found"],
            [$genreId1, $genreId2, [], "Genres $genreId1, $genreId2 not found"]
        ];
    }
    // função que testa o método de execução, sem sucesso na validação de genres
    // utiliza o dataProvider dataProviderExecuteGenreValidationFail
    /**
     * @dataProvider dataProviderExecuteGenreValidationFail
     */
    public function testExecuteGenreValidationFail(string $genreId1, string $genreId2, array $returnGenreRepository, string $exceptionMessage)
    {
        // definindo os atributos a serem utilizados nos mocks
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $opened = false;
        $rating = Rating::RATE10;
        $nameCategory = 'Category Name';
        $descriptionCategory = 'Category Description';
        $isActiveCategory = true;
        $nameCastMember = 'Cast Member Name';
        $typeCastMember = CastMemberType::ACTOR;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $castMemberId1 = Uuid::uuid4()->toString();
        $castMemberId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $genresId = [$genreId1, $genreId2];
        $castMembersId = [$castMemberId1, $castMemberId2];
        $thumbFile = ['tmp' => 'tmp/thumbFile.png'];
        $thumbHalf = ['tmp' => 'tmp/thumbHalf.png'];
        $bannerFile = ['tmp' => 'tmp/bannerFile.png'];
        $trailerFile = ['tmp' => 'tmp/trailerFile.mp4'];
        $videoFile = ['tmp' => 'tmp/videoFile.mp4'];

        // criando o mock da categoria 1
        $mockCategory1 = MocksFactory::createCategoryMock($categoryId1, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock da categoria 2
        $mockCategory2 = MocksFactory::createCategoryMock($categoryId2, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock do cast member 1
        $mockCastMember1 = MocksFactory::createCastMemberMock($castMemberId1, $nameCastMember, $typeCastMember);

        // criando o mock do cast member 2
        $mockCastMember2 = MocksFactory::createCastMemberMock($castMemberId2, $nameCastMember, $typeCastMember);

        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = self::createUpdateVideoInputDto(
            $initialVideo->id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating,
            $opened,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile
        );

        // criando a entidade com os dados do input
        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);
        $entity = $videoBuilder->getEntity();

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(0)->andReturn($initialVideo); //definindo o retorno do insert()
        $mockRepository->shouldReceive('update')->times(0)->andReturn($entity); //definindo o retorno do insert()
        $mockRepository->shouldReceive('updateMedia')->times(0)->andReturn($entity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(0)->andReturn('path_do_storage'); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times(0)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn($returnGenreRepository); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $mockRepository,
            $mockTransactionDb,
            $mockFileStorage,
            $mockEventManager,
            $mockCategoryRepository,
            $mockGenreRepository,
            $mockcastMemberRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);
    }

    // provedor de dados do testExecuteGenreValidationFail
    public function dataProviderExecuteCastMembersValidationFail(): array
    {
        $castMemberId1 = Uuid::uuid4()->toString();
        $castMemberId2 = Uuid::uuid4()->toString();
        $nameCastMember = 'Cast Member Name';
        $typeCastMember = CastMemberType::ACTOR;
        $mockCastMember1 = MocksFactory::createCastMemberMock($castMemberId1, $nameCastMember, $typeCastMember);
        $mockCastMember2 = MocksFactory::createCastMemberMock($castMemberId2, $nameCastMember, $typeCastMember);

        return [
            [$castMemberId1, $castMemberId2, [$mockCastMember1], "Cast Member $castMemberId2 not found"],
            [$castMemberId1, $castMemberId2, [], "Cast Members $castMemberId1, $castMemberId2 not found"]
        ];
    }
    // função que testa o método de execução, sem sucesso na validação de cast members
    // utiliza o dataProvider dataProviderExecuteCastMembersValidationFail
    /**
     * @dataProvider dataProviderExecuteCastMembersValidationFail
     */
    public function testExecuteCastMembersValidationFail(string $castMemberId1, string $castMemberId2, array $returnCastMemberRepository, string $exceptionMessage)
    {
        // definindo os atributos a serem utilizados nos mocks
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $opened = false;
        $rating = Rating::RATE10;
        $nameCategory = 'Category Name';
        $descriptionCategory = 'Category Description';
        $isActiveCategory = true;
        $nameGenre = 'Genre Name';
        $isActiveGenre = true;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $genreId1 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $genresId = [$genreId1];
        $castMembersId = [$castMemberId1, $castMemberId2];
        $thumbFile = ['tmp' => 'tmp/thumbFile.png'];
        $thumbHalf = ['tmp' => 'tmp/thumbHalf.png'];
        $bannerFile = ['tmp' => 'tmp/bannerFile.png'];
        $trailerFile = ['tmp' => 'tmp/trailerFile.mp4'];
        $videoFile = ['tmp' => 'tmp/videoFile.mp4'];

        // criando o mock da categoria 1
        $mockCategory1 = MocksFactory::createCategoryMock($categoryId1, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock da categoria 2
        $mockCategory2 = MocksFactory::createCategoryMock($categoryId2, $nameCategory, $descriptionCategory, $isActiveCategory);

        // criando o mock do genre 1
        $mockGenre1 = MocksFactory::createGenreMock($genreId1, $nameGenre, $isActiveGenre, $categoriesId);

        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = self::createUpdateVideoInputDto(
            $initialVideo->id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating,
            $opened,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile
        );

        // criando a entidade com os dados do input
        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);
        $entity = $videoBuilder->getEntity();

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(0)->andReturn($initialVideo); //definindo o retorno do insert()
        $mockRepository->shouldReceive('update')->times(0)->andReturn($entity); //definindo o retorno do insert()
        $mockRepository->shouldReceive('updateMedia')->times(0)->andReturn($entity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(0)->andReturn('path_do_storage'); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times(0)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockGenre1]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(1)->andReturn($returnCastMemberRepository); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $mockRepository,
            $mockTransactionDb,
            $mockFileStorage,
            $mockEventManager,
            $mockCategoryRepository,
            $mockGenreRepository,
            $mockcastMemberRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);
    }

    // função auxiliar para criação do video inicial
    private static function createInitialVideo(): Video
    {
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
            thumbFile: ['Original thumbFile'],
            thumbHalf: ['Original thumbHalf'],
            bannerFile: ['Original bannerFile'],
            trailerFile: ['Original trailerFile'],
            videoFile: ['Original videoFile']
        );
        $video = ((new CreateVideoBuilder)->createEntity($inputDto))->getEntity();

        return $video;
    }

    // função auxiliar para criação do input dto
    private static function createUpdateVideoInputDto(
        string $id,
        string $title,
        string $description,
        int $yearLaunched,
        int $duration,
        Rating $rating,
        bool $opened,
        array  $categoriesId,
        array  $genresId,
        array  $castMembersId,
        array $thumbFile = [],
        array $thumbHalf = [],
        array $bannerFile = [],
        array $trailerFile = [],
        array $videoFile = []
    ): UpdateVideoInputDto {
        $dto = new UpdateVideoInputDto(
            $id,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $opened,
            $rating,
            $categoriesId,
            $genresId,
            $castMembersId,
            $thumbFile,
            $thumbHalf,
            $bannerFile,
            $trailerFile,
            $videoFile,
        );

        return $dto;
    }

    // método para encerrar os mocks
    protected function tearDown(): void
    {
        // encerrando os mocks
        Mockery::close();

        parent::tearDown();
    }
}
