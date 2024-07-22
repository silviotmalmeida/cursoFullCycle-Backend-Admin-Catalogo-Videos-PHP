<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Entity\Video;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\UseCase\Video\DeleteById\DeleteByIdVideoUseCase;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoInputDto;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoOutputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\MocksFactory;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class DeleteByIdVideoUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução com sucesso
    public function testExecuteTrue()
    {
        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = new DeleteByIdVideoInputDto($initialVideo->id());

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($initialVideo->id())->andReturn($initialVideo); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($initialVideo->id())->andReturn(true); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdVideoUseCase(
            $mockRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdVideoOutputDto::class, $responseUseCase);
        $this->assertTrue($responseUseCase->sucess);
    }

    // função que testa o método de execução sem sucesso
    public function testExecuteFalse()
    {
        // criando o video inicial
        $initialVideo = self::createInitialVideo();

        // criando o inputDto
        $inputDto = new DeleteByIdVideoInputDto($initialVideo->id());

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($initialVideo->id())->andReturn($initialVideo); //definindo o retorno do findById()
        $mockRepository->shouldReceive('deleteById')->times(1)->with($initialVideo->id())->andReturn(false); //definindo o retorno do deleteById()

        // criando o usecase
        $useCase = new DeleteByIdVideoUseCase(
            $mockRepository
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdVideoOutputDto::class, $responseUseCase);
        $this->assertFalse($responseUseCase->sucess);
    }

    // função auxiliar para criação do video inicial
    private static function createInitialVideo(): Video
    {
        $inputDto = new InsertVideoInputDto(
            title: 'Original Title',
            description: 'Original Description',
            yearLaunched: 2023,
            duration: 50,
            rating: Rating::RATE10,
            opened: false,
            categoriesId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
            genresId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
            castMembersId: [Uuid::uuid4()->toString(), Uuid::uuid4()->toString(), Uuid::uuid4()->toString()],
        );

        $videoBuilder = (new CreateVideoBuilder)->createEntity($inputDto);

        return $videoBuilder->getEntity();
    }

    // método para encerrar os mocks
    protected function tearDown(): void
    {
        // encerrando os mocks
        Mockery::close();

        parent::tearDown();
    }
}
