<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\CastMember;

// importações

use Core\Domain\Entity\Category;
use Core\Domain\Entity\CastMember;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\UseCase\CastMember\InsertCastMemberUseCase;
use Core\UseCase\DTO\CastMember\InsertCastMember\InsertCastMemberInputDto;
use Core\UseCase\DTO\CastMember\InsertCastMember\InsertCastMemberOutputDto;
use Core\UseCase\Interfaces\TransactionDbInterface;
use DateTime;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class InsertCastMemberUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução, com sucesso
    public function testExecute()
    {
        // definindo os atributos a serem utilizados nos mocks
        $uuid = Uuid::uuid4()->toString();
        $name = 'name';
        $type = CastMemberType::ACTOR;
        $now = (new DateTime())->format('Y-m-d H:i:s');

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(InsertCastMemberInputDto::class, [
            $name,
            $type,
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
        $mockRepository->shouldReceive('insert')->times(1)->andReturn($mockEntity); //definindo o retorno do insert()

        // criando o usecase
        $useCase = new InsertCastMemberUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertCastMemberOutputDto::class, $responseUseCase);
        $this->assertSame($uuid, $responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($type->value, $responseUseCase->type);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        // encerrando os mocks
        Mockery::close();
    }
}
