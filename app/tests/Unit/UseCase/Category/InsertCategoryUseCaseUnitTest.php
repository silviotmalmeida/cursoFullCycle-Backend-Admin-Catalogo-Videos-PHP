<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Category\CreateCategoryUseCase;
use Core\UseCase\DTO\Category\CreateCategory\CategoryCreateInputDto;
use Core\UseCase\DTO\Category\CreateCategory\CategoryCreateOutputDto;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class InsertCategoryUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // criando o mock da entidade
        $uuid = (string) Uuid::uuid4()->toString();
        $categoryName = 'name cat';
        $this->mockEntity = Mockery::mock(Category::class, [
            $uuid,
            $categoryName,
        ]);
        $this->mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()

        // criando o mock do repository
        $this->mockRepo = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $this->mockRepo->shouldReceive('insert')->andReturn($this->mockEntity); //definindo o retorno do insert()

        // criando o mock do inputDto
        $this->mockInputDto = Mockery::mock(CategoryCreateInputDto::class, [
            $categoryName,
        ]);

        $useCase = new CreateCategoryUseCase($this->mockRepo);
        $responseUseCase = $useCase->execute($this->mockInputDto);

        $this->assertInstanceOf(CategoryCreateOutputDto::class, $responseUseCase);
        $this->assertEquals($categoryName, $responseUseCase->name);
        $this->assertEquals('', $responseUseCase->description);

        /**
         * Spies
         */
        $this->spy = Mockery::spy(stdClass::class, CategoryRepositoryInterface::class);
        $this->spy->shouldReceive('insert')->andReturn($this->mockEntity);
        $useCase = new CreateCategoryUseCase($this->spy);
        $responseUseCase = $useCase->execute($this->mockInputDto);
        $this->spy->shouldHaveReceived('insert');

        Mockery::close();
    }
}
