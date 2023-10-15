<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Category\DeleteCategoryUseCase;
use Core\UseCase\DTO\Category\DeleteCategory\DeleteCategoryInputDto;
use Core\UseCase\DTO\Category\DeleteCategory\DeleteCategoryOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DeleteCategoryUseCaseUnitTest extends TestCase
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
        $mockInputDto = Mockery::mock(DeleteCategoryInputDto::class, [
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
        $mockRepository->shouldReceive('findById')->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('delete')->andReturn(true); //definindo o retorno do delete()

        // criando o usecase
        $useCase = new DeleteCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        // criando o spy do repository
        $spyRepository = Mockery::spy(stdClass::class, CategoryRepositoryInterface::class);
        $spyRepository->shouldReceive('findById')->andReturn($mockEntity); //definindo o retorno do findById()
        $spyRepository->shouldReceive('delete')->andReturn(true); //definindo o retorno do delete()

        // criando o usecase
        $useCase = new DeleteCategoryUseCase($spyRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando a utilização dos métodos
        $spyRepository->shouldHaveReceived('findById');
        $spyRepository->shouldHaveReceived('delete');

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
        $mockInputDto = Mockery::mock(DeleteCategoryInputDto::class, [
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
        $mockRepository->shouldReceive('findById')->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('delete')->andReturn(false); //definindo o retorno do delete()

        // criando o usecase
        $useCase = new DeleteCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(false, $responseUseCase->sucess);

        // criando o spy do repository
        $spyRepository = Mockery::spy(stdClass::class, CategoryRepositoryInterface::class);
        $spyRepository->shouldReceive('findById')->andReturn($mockEntity); //definindo o retorno do findById()
        $spyRepository->shouldReceive('delete')->andReturn(false); //definindo o retorno do delete()

        // criando o usecase
        $useCase = new DeleteCategoryUseCase($spyRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando a utilização dos métodos
        $spyRepository->shouldHaveReceived('findById');
        $spyRepository->shouldHaveReceived('delete');

        // encerrando os mocks
        Mockery::close();
    }
}
