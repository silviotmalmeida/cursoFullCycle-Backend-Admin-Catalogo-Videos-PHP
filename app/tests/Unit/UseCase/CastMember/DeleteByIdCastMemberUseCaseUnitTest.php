<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\CastMember;

// importações
use Core\Domain\Entity\CastMember;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\CastMember\DeleteByIdCastMemberUseCase;
use Core\UseCase\DTO\CastMember\DeleteByIdCastMember\DeleteByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\DeleteByIdCastMember\DeleteByIdCastMemberOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DeleteByIdCastMemberUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução com sucesso
    public function testExecuteTrue()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $type = CastMemberType::ACTOR;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdCastMemberInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(CastMember::class, [
            $uuid,
            $name,
            $type,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(true); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdCastMemberUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdCastMemberOutputDto::class, $responseUseCase);
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
        $type = CastMemberType::DIRECTOR;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(DeleteByIdCastMemberInputDto::class, [
            $uuid,
        ]);

        // criando o mock da entidade
        $mockEntity = Mockery::mock(CastMember::class, [
            $uuid,
            $name,
            $type,
        ]);
        $mockEntity->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntity->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntity->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        // criando o mock do repository
        $mockRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($uuid)->andReturn(false); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdCastMemberUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdCastMemberOutputDto::class, $responseUseCase);
        $this->assertSame(false, $responseUseCase->sucess);

        // encerrando os mocks
        Mockery::close();
    }
}
