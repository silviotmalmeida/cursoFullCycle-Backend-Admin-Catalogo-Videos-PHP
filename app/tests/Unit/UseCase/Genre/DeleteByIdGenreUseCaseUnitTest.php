<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Genre;

// importações
use Core\Domain\Entity\Genre;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Genre\DeleteByIdGenreUseCase;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreInputDto;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DeleteByIdGenreUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução com sucesso
    public function testExecuteTrue()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name genre';
        $isActive = false;
        $categoriesId = [Uuid::uuid4()->toString(), Uuid::uuid4()->toString()];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdGenreInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Genre::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(true); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdGenreUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdGenreOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        // encerrando os mocks
        Mockery::close();
    }

    // função que testa o método de execução sem sucesso
    public function testExecuteFalse()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name genre';
        $isActive = false;
        $categoriesId = [Uuid::uuid4()->toString(), Uuid::uuid4()->toString()];
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdGenreInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Genre::class, [
            $uuid,
            $name,
            $isActive,
            $categoriesId,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(GenreRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(false); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdGenreUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdGenreOutputDto::class, $responseUseCase);
        $this->assertSame(false, $responseUseCase->sucess);

        // encerrando os mocks
        Mockery::close();
    }
}
