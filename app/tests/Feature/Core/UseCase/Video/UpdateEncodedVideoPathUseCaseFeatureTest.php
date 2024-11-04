<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\Core\UseCase\Video;

// importações
use App\Models\CastMember as CastMemberModel;
use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use App\Models\Video as VideoModel;
use App\Repositories\Eloquent\CastMemberEloquentRepository;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Eloquent\GenreEloquentRepository;
use App\Repositories\Eloquent\VideoEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use App\Services\Storage\FileStorage;
use Core\Domain\Entity\Video;
use Core\Domain\Exception\NotFoundException;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\UpdateVideoUseCase;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathInputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\DTO\UpdateEncodedVideoPathOutputDto;
use Core\UseCase\Video\UpdateEncodedVideoPath\UpdateEncodedVideoPathUseCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\VideoEventManagerStub;
use Tests\TestCase;

class UpdateEncodedVideoPathUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $video = $this->createVideoWithMedia();
        // verificando os registros iniciais no BD
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $video->id(),
            'file_path' => $video->videoFile()->filePath(),
            'encoded_path' => ''
        ]);

        // criando o inputDto
        $inputDto = new UpdateEncodedVideoPathInputDto(
            $video->id(),
            'path/video_encoded.ext'
        );

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new UpdateEncodedVideoPathUseCase(
            $repository,
        );

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateEncodedVideoPathOutputDto::class, $responseUseCase);
        $this->assertSame($video->id(), $responseUseCase->id);
        $this->assertSame($inputDto->encodedPath, $responseUseCase->encodedPath);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $video->title,
            'description' => $video->description,
            'year_launched' => $video->yearLaunched,
            'duration' => $video->duration,
            'opened' => $video->opened,
            'rating' => $video->rating,
        ]);

        // verificando se o arquivo de media foi registrado no bd
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $responseUseCase->id,
            'file_path' => $video->videoFile()->filePath(),
            'encoded_path' => $responseUseCase->encodedPath,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($video->videoFile()->filePath());

        // apagando os arquivos armazenados
        Storage::deleteDirectory($responseUseCase->id);
    }

    // função que testa o método de execução em vídeo sem mídia associada
    public function testExecuteWithoutMedia()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        // verificando os registros iniciais no BD
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_medias', 0);

        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Não existe arquivo de video associado ao vídeo de id {$model->id}");
               
        // criando o inputDto
        $inputDto = new UpdateEncodedVideoPathInputDto(
            $model->id,
            'path/video_encoded.ext'
        );

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new UpdateEncodedVideoPathUseCase(
            $repository,
        );

        // executando o usecase
        $useCase->execute($inputDto);
    }

    // função auxiliar para criar um video com a mídia associada
    private function createVideoWithMedia(): Video
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do videofile
        $fakeVideoFile = UploadedFile::fake()->create('videofile.mp4', 1, 'video/mp4');
        $videofile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            videoFile: $videofile,
        );

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o gerenciador de storage
        $fileStorage = new FileStorage();

        // criando o gerenciador de eventos
        $eventManager = new VideoEventManagerStub();

        // criando o repository de Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o repository de Genre
        $genreRepository = new GenreEloquentRepository(new GenreModel());

        // criando o repository de CastMember
        $castMemberRepository = new CastMemberEloquentRepository(new CastMemberModel());

        // criando o usecase
        $useCase = new UpdateVideoUseCase(
            $repository,
            $transactionDb,
            $fileStorage,
            $eventManager,
            $categoryRepository,
            $genreRepository,
            $castMemberRepository,
        );

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // retornando a entidade
        return $repository->findById($responseUseCase->id);
    }
}
