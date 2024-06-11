<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações

use Core\Domain\Entity\CastMember;
use Core\Domain\Entity\Category;
use Core\Domain\Entity\Genre;
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
use DateTime;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da categoria 1
        $mockCategory1 = Mockery::mock(Category::class, [
            $categoryId1,
            $nameCategory,
            $description,
            $isActiveCategory,
        ]);
        $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
        $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock da categoria 2
        $mockCategory2 = Mockery::mock(Category::class, [
            $categoryId2,
            $nameCategory,
            $description,
            $isActiveCategory,
        ]);
        $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
        $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do genre 1
        $mockGenre1 = Mockery::mock(Genre::class, [
            $genreId1,
            $nameGenre,
            $isActiveGenre,
            $categoriesId
        ]);
        $mockGenre1->shouldReceive('id')->andReturn($genreId1); //definindo o retorno do id()
        $mockGenre1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockGenre1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()


        // criando o mock do cast member 1
        $mockCastMember1 = Mockery::mock(CastMember::class, [
            $castMemberId1,
            $nameCastMember,
            $typeCastMember,
        ]);
        $mockCastMember1->shouldReceive('id')->andReturn($castMemberId1); //definindo o retorno do id()
        $mockCastMember1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCastMember1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do cast member 2
        $mockCastMember2 = Mockery::mock(CastMember::class, [
            $castMemberId2,
            $nameCastMember,
            $typeCastMember,
        ]);
        $mockCastMember2->shouldReceive('id')->andReturn($castMemberId2); //definindo o retorno do id()
        $mockCastMember2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCastMember2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
            $title, $description, $yearLaunched, $duration, $opened, $rating
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Video::class, [
            $uuid, $title, $description, $yearLaunched, $duration, $opened, $rating
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($mockEntity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(1)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(0)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do fileStorage
        $mockFileStorage = Mockery::mock(FileStorageInterface::class);
        $mockFileStorage->shouldReceive('store')->times(1)->andReturn('path_do_storage ' . $uuid); //definindo o retorno do store()
        $mockFileStorage->shouldReceive('delete')->times(0)->andReturn(true); //definindo o retorno do delete()

        // criando o mock do eventManager
        $mockEventManager = Mockery::mock(VideoEventManagerInterface::class);
        $mockEventManager->shouldReceive('dispatch')->times(1)->andReturn(); //definindo o retorno do dispatch()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do insert()

        // criando o mock do genreRepository
        $mockGenreRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockGenreRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockGenre1]); //definindo o retorno do insert()

        // criando o mock do castMemberRepository
        $mockcastMemberRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockcastMemberRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCastMember1, $mockCastMember2]); //definindo o retorno do insert()

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
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($opened, $responseUseCase->opened);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }

    // // função que testa o método de execução, sem sucesso e com rollback
    // public function testExecuteRollback()
    // {
    //     // definindo os atributos a serem utilizados nos mocks
    //     $uuid = Uuid::uuid4()->toString();
    //     $name = 'name';
    //     $description = 'description';
    //     $isActive = false;
    //     $categoryId1 = Uuid::uuid4()->toString();
    //     $categoryId2 = Uuid::uuid4()->toString();
    //     $categoriesId = [$categoryId1, $categoryId2];
    //     $now = (new DateTime())->format('Y-m-d H:i:s');

    //     // criando o mock da categoria 1
    //     $mockCategory1 = Mockery::mock(Category::class, [
    //         $categoryId1,
    //         $name,
    //         $description,
    //         $isActive,
    //     ]);
    //     $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
    //     $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock da categoria 2
    //     $mockCategory2 = Mockery::mock(Category::class, [
    //         $categoryId2,
    //         $name,
    //         $description,
    //         $isActive,
    //     ]);
    //     $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
    //     $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock do inputDto
    //     $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
    //         $name,
    //         $isActive,
    //         $categoriesId
    //     ]);

    //     // criando o mock da entidade
    //     $mockEntity = Mockery::mock(Video::class, [
    //         $uuid,
    //         $name,
    //         $isActive,
    //         $categoriesId
    //     ]);
    //     $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
    //     $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock do repository
    //     $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
    //     $mockRepository->shouldReceive('insert')->times(1)->andReturn($mockEntity); //definindo o retorno do insert()

    //     // criando o mock do transactionDb
    //     $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
    //     $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
    //     $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

    //     // criando o mock do categoryRepository
    //     $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    //     $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do insert()

    //     // tratamento de exceções
    //     try {
    //         // criando o usecase
    //         $useCase = new InsertVideoUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
    //         // executando o usecase
    //         $useCase->execute($mockInputDto, true);
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(Exception::class, $th);
    //         $this->assertEquals($th->getMessage(), "rollback test");
    //     } finally {
    //         // encerrando os mocks
    //         Mockery::close();
    //     }
    // }

    // // função que testa o método de execução, sem sucesso na validação de categorias
    // public function testExecuteCategoriesValidationFail()
    // {
    //     // definindo os atributos a serem utilizados nos mocks
    //     $uuid = Uuid::uuid4()->toString();
    //     $name = 'name';
    //     $description = 'description';
    //     $isActive = false;
    //     $categoryId1 = Uuid::uuid4()->toString();
    //     $categoryId2 = Uuid::uuid4()->toString();
    //     $categoriesId = [$categoryId1, $categoryId2];
    //     $now = (new DateTime())->format('Y-m-d H:i:s');

    //     // criando o mock da categoria 1
    //     $mockCategory1 = Mockery::mock(Category::class, [
    //         $categoryId1,
    //         $name,
    //         $description,
    //         $isActive,
    //     ]);
    //     $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
    //     $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock da categoria 2
    //     $mockCategory2 = Mockery::mock(Category::class, [
    //         $categoryId2,
    //         $name,
    //         $description,
    //         $isActive,
    //     ]);
    //     $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
    //     $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock do inputDto
    //     $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
    //         $name,
    //         $isActive,
    //         $categoriesId
    //     ]);

    //     // criando o mock da entidade
    //     $mockEntity = Mockery::mock(Video::class, [
    //         $uuid,
    //         $name,
    //         $isActive,
    //         $categoriesId
    //     ]);
    //     $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
    //     $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
    //     $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

    //     // criando o mock do repository
    //     $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
    //     $mockRepository->shouldReceive('insert')->times(0)->andReturn($mockEntity); //definindo o retorno do insert()

    //     // criando o mock do transactionDb
    //     $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
    //     $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
    //     $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

    //     // criando o mock do categoryRepository
    //     $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    //     $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([]); //definindo o retorno do insert()

    //     // tratamento de exceções
    //     try {
    //         // criando o usecase
    //         $useCase = new InsertVideoUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
    //         // executando o usecase
    //         $responseUseCase = $useCase->execute($mockInputDto);
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(NotFoundException::class, $th);
    //         $this->assertEquals($th->getMessage(), "Categories $categoryId1, $categoryId2 not found");
    //     } finally {
    //         // encerrando os mocks
    //         Mockery::close();
    //     }
    // }
}
