<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\CastMember;

// importações
use Core\Domain\Entity\CastMember;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\CastMember\UpdateCastMemberUseCase;
use Core\UseCase\DTO\CastMember\UpdateCastMember\UpdateCastMemberInputDto;
use Core\UseCase\DTO\CastMember\UpdateCastMember\UpdateCastMemberOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateCastMemberUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $updatedName = 'updated name cat';
        $type = CastMemberType::ACTOR;
        $updatedType = CastMemberType::DIRECTOR;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(UpdateCastMemberInputDto::class, [
            $uuid,
            $updatedName,
            $updatedType,
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
        $mockEntity->shouldReceive('update')->times(1)->with($updatedName, $updatedType)->andReturn(); //definindo o retorno do update()

        // criando o mock da entidade atualizada
        $mockEntityUpdated = Mockery::mock(CastMember::class, [
            $uuid,
            $updatedName,
            $updatedType,
        ]);
        sleep(1);
        $nowUpdated = (new DateTime())->format('Y-m-d H:i:s');
        $mockEntityUpdated->shouldReceive('id')->andReturn($uuid); //definindo o retorno do id()
        $mockEntityUpdated->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mockEntityUpdated->shouldReceive('updatedAt')->andReturn($nowUpdated); //definindo o retorno do updatedAt()
        $mockEntity->shouldReceive('update')->times(0)->with($updatedName, $updatedType)->andReturn(); //definindo o retorno do update()

        // criando o mock do repository
        $mockRepository = Mockery::mock(CastMemberRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($uuid)->andReturn($mockEntity); //definindo o retorno do findById()
        $mockRepository->shouldReceive('update')->times(1)->andReturn($mockEntityUpdated); //definindo o retorno do update()

        // criando o usecase
        $useCase = new UpdateCastMemberUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateCastMemberOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($updatedName, $responseUseCase->name);
        $this->assertSame($updatedType->value, $responseUseCase->type);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
