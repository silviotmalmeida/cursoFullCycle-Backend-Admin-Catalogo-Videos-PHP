<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Genre;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Genre\InsertGenreUseCase;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreInputDto;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class InsertGenreUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name genre';
        $isActive = false;
        $categoriesId = [Uuid::uuid4()->toString(), Uuid::uuid4()->toString()];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertGenreInputDto::class, [
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

        // criando o mock do repository
        $mockRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($mockEntity); //definindo o retorno do insert()

        // criando o usecase
        $useCase = new InsertGenreUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertGenreOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertSame($categoriesId, $responseUseCase->categories_id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
