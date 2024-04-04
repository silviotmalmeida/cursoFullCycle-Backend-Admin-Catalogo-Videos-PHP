<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\CastMember;

// importações
use Core\Domain\Entity\CastMember;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\CastMember\FindByIdCastMemberUseCase;
use Core\UseCase\DTO\CastMember\FindByIdCastMember\FindByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\FindByIdCastMember\FindByIdCastMemberOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class FindByIdCastMemberUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name';
        $type = CastMemberType::ACTOR;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(FindByIdCastMemberInputDto::class, [
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

        // criando o usecase
        $useCase = new FindByIdCastMemberUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdCastMemberOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($type->value, $responseUseCase->type);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
