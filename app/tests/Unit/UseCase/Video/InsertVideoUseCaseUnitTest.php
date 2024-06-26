<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações

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
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
use Core\UseCase\Video\Insert\InsertVideoUseCase;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MocksFactory;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class InsertVideoUseCaseUnitTest extends TestCase
{

    // função que testa o método de execução, com sucesso
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
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

        // criando a entidade
        $entity = $this->createVideoEntity($uuid, $title, $description, $yearLaunched, $duration, $rating, $opened, $categoriesId, $genresId, $castMembersId);

        // criando o inputDto
        // $inputDto = MocksFactory::creatInsertVideoInputDtoMock($entity);
        $inputDto = self::createInsertVideoInputDto($entity);

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($entity); //definindo o retorno do insert()
        $mockRepository->shouldReceive('updateMedia')->times(1)->andReturn($entity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(1)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(0)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(5)->andReturn('path_do_storage'); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

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

        // criando o usecase
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($opened, $responseUseCase->opened);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNull($responseUseCase->thumbFile);
        $this->assertNull($responseUseCase->thumbHalf);
        $this->assertNull($responseUseCase->bannerFile);
        $this->assertNull($responseUseCase->trailerFile);
        $this->assertNull($responseUseCase->videoFile);
        $this->assertSame($categoriesId, $responseUseCase->categoriesId);
        $this->assertSame($genresId, $responseUseCase->genresId);
        $this->assertSame($castMembersId, $responseUseCase->castMembersId);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução, sem sucesso e com rollback
    public function testExecuteRollback()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
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

        // criando a entidade
        $entity = $this->createVideoEntity($uuid, $title, $description, $yearLaunched, $duration, $rating, $opened, $categoriesId, $genresId, $castMembersId);

        // criando o inputDto
        // $inputDto = MocksFactory::creatInsertVideoInputDtoMock($entity);
        $inputDto = self::createInsertVideoInputDto($entity);

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($entity); //definindo o retorno do insert()
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
        $useCase = new InsertVideoUseCase(
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

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução, sem sucesso na validação de categorias
    public function testExecuteCategoriesValidationFail()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
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

        // criando a entidade
        $entity = $this->createVideoEntity($uuid, $title, $description, $yearLaunched, $duration, $rating, $opened, $categoriesId, $genresId, $castMembersId);

        // criando o inputDto
        // $inputDto = MocksFactory::creatInsertVideoInputDtoMock($entity);
        $inputDto = self::createInsertVideoInputDto($entity);

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(0)->andReturn($entity); //definindo o retorno do insert()
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
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([]); //definindo o retorno do findByIdArray()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockGenre1]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Categories $categoryId1, $categoryId2 not found");

        // criando o usecase
        $useCase = new InsertVideoUseCase(
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

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução, sem sucesso na validação de genres
    public function testExecuteGenreValidationFail()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
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

        // criando a entidade
        $entity = $this->createVideoEntity($uuid, $title, $description, $yearLaunched, $duration, $rating, $opened, $categoriesId, $genresId, $castMembersId);

        // criando o inputDto
        // $inputDto = MocksFactory::creatInsertVideoInputDtoMock($entity);
        $inputDto = self::createInsertVideoInputDto($entity);

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(0)->andReturn($entity); //definindo o retorno do insert()
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
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn([]); //definindo o retorno do findByIdArray()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(0)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Genre $genreId1 not found");

        // criando o usecase
        $useCase = new InsertVideoUseCase(
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

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução, sem sucesso na validação de cast members
    public function testExecuteCastMembersValidationFail()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
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

        // criando a entidade
        $entity = $this->createVideoEntity($uuid, $title, $description, $yearLaunched, $duration, $rating, $opened, $categoriesId, $genresId, $castMembersId);

        // criando o inputDto
        // $inputDto = MocksFactory::creatInsertVideoInputDtoMock($entity);
        $inputDto = self::createInsertVideoInputDto($entity);

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(0)->andReturn($entity); //definindo o retorno do insert()
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
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(1)->andReturn([]); //definindo o retorno do findByIdArray()

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Cast Members $castMemberId1, $castMemberId2 not found");

        // criando o usecase
        $useCase = new InsertVideoUseCase(
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

        // encerrando os mocks
        Mockery::close();
    }

    // função auxiliar para criação da entidade video
    private static function createVideoEntity(
        string $uuid,
        string $title,
        string $description,
        int $yearLaunched,
        int $duration,
        Rating $rating,
        bool $opened,
        array  $categoriesId,
        array  $genresId,
        array  $castMembersId,
    ): Video {

        $entity = new Video(
            $uuid,
            $title,
            $description,
            $yearLaunched,
            $duration,
            $rating
        );
        if ($opened) $entity->open();
        foreach ($categoriesId as $categoryId) {

            $entity->addCategoryId($categoryId);
        }
        foreach ($genresId as $genreId) {

            $entity->addGenreId($genreId);
        }
        foreach ($castMembersId as $castMemberId) {

            $entity->addCastMemberId($castMemberId);
        }

        return $entity;
    }

    // função auxiliar para criação do input dto
    private static function createInsertVideoInputDto(Video $entity): InsertVideoInputDto
    {
        $dto = new InsertVideoInputDto(
            $entity->title,
            $entity->description,
            $entity->yearLaunched,
            $entity->duration,
            $entity->opened,
            $entity->rating,
            $entity->categoriesId,
            $entity->genresId,
            $entity->castMembersId,
            ['thumb', 'file'],
            ['thumb', 'half'],
            ['banner', 'file'],
            ['trailer', 'file'],
            ['video', 'file'],
        );

        return $dto;
    }
}
