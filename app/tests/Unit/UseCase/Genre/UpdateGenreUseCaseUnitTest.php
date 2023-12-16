<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Genre;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Entity\Genre;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Genre\UpdateGenreUseCase;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreInputDto;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreOutputDto;
use Core\UseCase\Intefaces\TransactionDbInterface;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateGenreUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução, com sucesso
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $cat1 = Uuid::uuid4()->toString();
        $cat2 = Uuid::uuid4()->toString();
        $cat3 = Uuid::uuid4()->toString();
        $name = 'name';
        $updatedName = 'updated name';
        $description = 'description';
        $isActive = false;
        $updatedIsActive = true;
        $categoriesId = [$cat1, $cat2];
        $updatedCategoriesId = [$cat3];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da cat3
        $mockCat3 = Mockery::mock(Category::class, [
            $cat3,
            $name,
            $description,
            $isActive,
        ]);
        $mockCat3->shouldReceive('id')->andReturn($cat3); //definindo o retorno do id()
        $mockCat3->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCat3->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateGenreInputDto::class, [
            $uuid,
            $updatedName,
            $updatedIsActive,
            $updatedCategoriesId
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Genre::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(1)->with($updatedName, $updatedIsActive, $updatedCategoriesId)->andReturn(); //definindo o retorno do update()

        // criando o mock da entidade atualizada
        $mockEntityUpdated = Mockery::mock(Genre::class, [
            $uuid,
            $updatedName,
            $updatedIsActive,
            $updatedCategoriesId
        ]);
        sleep(1);
        $nowUpdated = (new DateTime())->format('Y-m-d H:i:s');
        $mockEntityUpdated->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntityUpdated->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntityUpdated->shouldReceive('updatedAt')->andReturn($nowUpdated); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(0)->with($updatedName, $updatedIsActive, $updatedCategoriesId)->andReturn(); //definindo o retorno do update()

        // criando o mock do repository
        $mockRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o mock do transactionDb
        $mockTransactionDb = Mockery::mock(TransactionDbInterface::class);
        $mockTransactionDb->shouldReceive('commit')->times(1)->andReturn(); //definindo o retorno do commit()
        $mockTransactionDb->shouldReceive('rollback')->times(0)->andReturn(); //definindo o retorno do rollback()

        // criando o mock do categoryRepository
        $mockCategoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockCategoryRepository->shouldReceive('findByIdArray')->times(1)->andReturn([$mockCat3]); //definindo o retorno do insert()

        // criando o usecase
        $useCase = new UpdateGenreUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($updatedName, $responseUseCase->name);
        $this->assertSame($updatedIsActive, $responseUseCase->is_active);
        $this->assertEquals($updatedCategoriesId, $responseUseCase->categories_id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução, sem sucesso na validação de categorias
    public function testExecuteCategoriesValidationFail()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $cat1 = Uuid::uuid4()->toString();
        $cat2 = Uuid::uuid4()->toString();
        $cat3 = Uuid::uuid4()->toString();
        $name = 'name';
        $updatedName = 'updated name';
        $description = 'description';
        $isActive = false;
        $updatedIsActive = true;
        $categoriesId = [$cat1, $cat2];
        $updatedCategoriesId = [$cat3];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock da cat3
        $mockCat3 = Mockery::mock(Category::class, [
            $cat3,
            $name,
            $description,
            $isActive,
        ]);
        $mockCat3->shouldReceive('id')->andReturn($cat3); //definindo o retorno do id()
        $mockCat3->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockCat3->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateGenreInputDto::class, [
            $uuid,
            $updatedName,
            $updatedIsActive,
            $updatedCategoriesId
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Genre::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(1)->with($updatedName, $updatedIsActive, $updatedCategoriesId)->andReturn(); //definindo o retorno do update()

        // criando o mock da entidade atualizada
        $mockEntityUpdated = Mockery::mock(Genre::class, [
            $uuid,
            $updatedName,
            $updatedIsActive,
            $updatedCategoriesId
        ]);
        sleep(1);
        $nowUpdated = (new DateTime())->format('Y-m-d H:i:s');
        $mockEntityUpdated->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntityUpdated->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntityUpdated->shouldReceive('updatedAt')->andReturn($nowUpdated); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(0)->with($updatedName, $updatedIsActive, $updatedCategoriesId)->andReturn(); //definindo o retorno do update()

        // criando o mock do repository
        $mockRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->with($uuid)->times(1)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(0)->andReturn($mockEntityUpdated); //definindo o retorno do update()

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
            $useCase = new UpdateGenreUseCase($mockRepository, $mockTransactionDb, $mockCategoryRepository);
            // executando o usecase
            $responseUseCase = $useCase->execute($mockInputDto);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            $this->assertEquals($th->getMessage(), "Category $cat3 not found");
        } finally {
            // encerrando os mocks
            Mockery::close();
        }
    }
}
