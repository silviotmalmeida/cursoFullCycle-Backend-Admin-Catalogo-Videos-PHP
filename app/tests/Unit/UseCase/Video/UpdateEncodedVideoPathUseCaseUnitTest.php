<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Video;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\Domain\ValueObject\Media;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathInputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathOutputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\UpdateEncodedVideoPathUseCase;
use Mockery;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class UpdateEncodedVideoPathUseCaseUnitTest extends TestCase
{

    // função que testa o método de execução, com sucesso
    public function testExecute()
    {
        // criando o video
        $video = new Video(
            title: 'New Title',
            description: 'New Description',
            yearLaunched: 2024,
            duration: 60,
            rating: Rating::RATE12,
        );
        
        // criando o videoFile
        $videoFile = new Media(
            filePath: 'path/videoFile.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::VIDEO,
            encodedPath: ''
        );
        // setando o videoFile
        $video->setVideoFile($videoFile);
        
        // criando o inputDto
        $inputDto = new UpdateEncodedVideoPathInputDto(
            $video->id(),
            'path/video_encoded.ext'
        );

        // criando o mock do repository
        $mockRepository = Mockery::mock(VideoRepositoryInterface::class);
        $mockRepository->shouldReceive('findById')->times(1)->with($video->id())->andReturn($video); //definindo o retorno do findById()
        $mockRepository->shouldReceive('updateMedia')->times(1); //definindo o retorno do updateMedia()

        // criando o usecase
        $useCase = new UpdateEncodedVideoPathUseCase(
            $mockRepository,
        );
        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateEncodedVideoPathOutputDto::class, $responseUseCase);
        $this->assertSame($video->id(), $responseUseCase->id);
        $this->assertSame($inputDto->encodedPath, $responseUseCase->encodedPath);
    }

    // método para encerrar os mocks
    protected function tearDown(): void
    {
        // encerrando os mocks
        Mockery::close();

        parent::tearDown();
    }
}
