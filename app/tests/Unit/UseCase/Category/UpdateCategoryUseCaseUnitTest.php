<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Category\UpdateCategoryUseCase;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryInputDto;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateCategoryUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $updatedName = 'updated name cat';
        $description = 'description cat';
        $updatedDescription = 'updated description cat';
        $isActive = false;
        $updatedIsActive = true;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateCategoryInputDto::class, [
            $uuid,
            $name,
            $description,
            $isActive,
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
        $mockEntity->shouldReceive('update')->times(1)->with($name, $description, $isActive)->andReturn(); //definindo o retorno do update()

        // criando o mock da entidade atualizada
        $mockEntityUpdated = Mockery::mock(Category::class, [
            $uuid,
            $updatedName,
            $updatedDescription,
            $updatedIsActive,
        ]);
        sleep(1);
        $nowUpdated = (new DateTime())->format('Y-m-d H:i:s');
        $mockEntityUpdated->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntityUpdated->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntityUpdated->shouldReceive('updatedAt')->andReturn($nowUpdated); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(0)->with($updatedName, $updatedDescription, $updatedIsActive)->andReturn(); //definindo o retorno do update()

        // criando o mock do repository
        $mockRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o usecase
        $useCase = new UpdateCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateCategoryOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($updatedName, $responseUseCase->name);
        $this->assertSame($updatedDescription, $responseUseCase->description);
        $this->assertSame($updatedIsActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
