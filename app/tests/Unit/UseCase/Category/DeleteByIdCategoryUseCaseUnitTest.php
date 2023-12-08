<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Category\DeleteByIdCategoryUseCase;
use Core\UseCase\DTO\Category\DeleteByIdCategory\DeleteByIdCategoryInputDto;
use Core\UseCase\DTO\Category\DeleteByIdCategory\DeleteByIdCategoryOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DeleteByIdCategoryUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução com sucesso
    public function testExecuteTrue()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $description = 'description cat';
        $isActive = false;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdCategoryInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Category::class, [
            $uuid,
            $name,
            $description,
            $isActive,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(true); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução sem sucesso
    public function testExecuteFalse()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $description = 'description cat';
        $isActive = false;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdCategoryInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Category::class, [
            $uuid,
            $name,
            $description,
            $isActive,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(false); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(false, $responseUseCase->sucess);

        // encerrando os mocks
        Mockery::close();
    }
}
