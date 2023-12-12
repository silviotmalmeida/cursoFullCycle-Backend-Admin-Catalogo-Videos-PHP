<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Category\FindByIdCategoryUseCase;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryInputDto;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class FindByIdCategoryUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $description = 'description cat';
        $isActive = false;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(FindByIdCategoryInputDto::class, [
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
        $mockRepository = Mockery::mock(CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()

        // criando o usecase
        $useCase = new FindByIdCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdCategoryOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
