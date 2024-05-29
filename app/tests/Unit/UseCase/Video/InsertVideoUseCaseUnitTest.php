<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações

use Core\Domain\Entity\Category;
use Core\Domain\Entity\Video;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\InsertVideoUseCase;
use Core\UseCase\DTO\Video\InsertVideo\InsertVideoInputDto;
use Core\UseCase\DTO\Video\InsertVideo\InsertVideoOutputDto;
use Core\UseCase\Interfaces\TransactionDbInterface;
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
        $name = 'name';
        $description = 'description';
        $isActive = false;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da categoria 1
        $mockCategory1 = Mockery::mock(Category::class, [
            $categoryId1,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
        $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock da categoria 2
        $mockCategory2 = Mockery::mock(Category::class, [
            $categoryId2,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
        $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
            $name,
            $isActive,
            $categoriesId
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Video::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
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

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do insert()

        // criando o usecase
        $useCase = new InsertVideoUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertSame($categoriesId, $responseUseCase->categories_id);
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
        $name = 'name';
        $description = 'description';
        $isActive = false;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da categoria 1
        $mockCategory1 = Mockery::mock(Category::class, [
            $categoryId1,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
        $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock da categoria 2
        $mockCategory2 = Mockery::mock(Category::class, [
            $categoryId2,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
        $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
            $name,
            $isActive,
            $categoriesId
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Video::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($mockEntity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCategory1, $mockCategory2]); //definindo o retorno do insert()

        // tratamento de exceções
        try {
            // criando o usecase
            $useCase = new InsertVideoUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
            // executando o usecase
            $useCase->execute($mockInputDto, true);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals($th->getMessage(), "rollback test");
        } finally {
            // encerrando os mocks
            Mockery::close();
        }
    }

    // função que testa o método de execução, sem sucesso na validação de categorias
    public function testExecuteCategoriesValidationFail()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name';
        $description = 'description';
        $isActive = false;
        $categoryId1 = Uuid::uuid4()->toString();
        $categoryId2 = Uuid::uuid4()->toString();
        $categoriesId = [$categoryId1, $categoryId2];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da categoria 1
        $mockCategory1 = Mockery::mock(Category::class, [
            $categoryId1,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory1->shouldReceive('id')->andReturn($categoryId1); //definindo o retorno do id()
        $mockCategory1->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory1->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock da categoria 2
        $mockCategory2 = Mockery::mock(Category::class, [
            $categoryId2,
            $name,
            $description,
            $isActive,
        ]);
        $mockCategory2->shouldReceive('id')->andReturn($categoryId2); //definindo o retorno do id()
        $mockCategory2->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCategory2->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertVideoInputDto::class, [
            $name,
            $isActive,
            $categoriesId
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Video::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(0)->andReturn($mockEntity); //definindo o retorno do insert()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(0)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(1)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([]); //definindo o retorno do insert()

        // tratamento de exceções
        try {
            // criando o usecase
            $useCase = new InsertVideoUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
            // executando o usecase
            $responseUseCase = $useCase->execute($mockInputDto);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            $this->assertEquals($th->getMessage(), "Categories $categoryId1, $categoryId2 not found");
        } finally {
            // encerrando os mocks
            Mockery::close();
        }
    }
}
