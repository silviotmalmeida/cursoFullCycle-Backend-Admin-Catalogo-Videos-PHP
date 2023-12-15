<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Genre;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Genre\UpdateGenreUseCase;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreInputDto;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateGenreUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $cat1 = Uuid::uuid4()->toString();
        $cat2 = Uuid::uuid4()->toString();
        $cat3 = Uuid::uuid4()->toString();
        $name = 'name genre';
        $updatedName = 'updated name genre';
        $isActive = false;
        $updatedIsActive = true;
        $categoriesId = [$cat1, $cat2];
        $updatedCategoriesId = [$cat3];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateGenreInputDto::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId
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
        $mockEntity->shouldReceive('update')->times(1)->with($name, $isActive, $categoriesId)->andReturn(); //definindo o retorno do update()

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
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o usecase
        $useCase = new UpdateGenreUseCase($mockRepository);
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
}
