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
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateCategoryInputDto::class, [
            $uuid,
            $name,
            $description,
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
        $mockEntity->shouldReceive('update')->andReturn(); //definindo o retorno do update()

        // criando o mock da entidade atualizada
        $mockEntityUpdated = Mockery::mock(Category::class, [
            $uuid,
            $updatedName,
            $updatedDescription,
            $isActive,
        ]);
        sleep(1);
        $nowUpdated = (new DateTime())->format('Y-m-d H:i:s');
        $mockEntityUpdated->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntityUpdated->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntityUpdated->shouldReceive('updatedAt')->andReturn($nowUpdated); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->andReturn(); //definindo o retorno do update()

        // criando o mock do repository
        $mockRepository = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o usecase
        $useCase = new UpdateCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateCategoryOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($updatedName, $responseUseCase->name);
        $this->assertSame($updatedDescription, $responseUseCase->description);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // criando o spy da entidade
        $spyEntity = Mockery::spy(Category::class, [
            $uuid,
            $name,
            $description,
            $isActive,
        ]);
        $spyEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $spyEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $spyEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()
        $spyEntity->shouldReceive('update')->andReturn(); //definindo o retorno do update()


        // criando o spy do repository
        $spyRepository = Mockery::spy(stdClass::class, CategoryRepositoryInterface::class);
        $spyRepository->shouldReceive('findById')->andReturn($spyEntity); //definindo o retorno do findById()
        $spyRepository->shouldReceive('update')->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o usecase
        $useCase = new UpdateCategoryUseCase($spyRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando a utilização dos métodos
        $spyEntity->shouldHaveReceived('update');
        $spyRepository->shouldHaveReceived('findById');
        $spyRepository->shouldHaveReceived('update');

        // encerrando os mocks
        Mockery::close();
    }
}
