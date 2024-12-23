<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Api\VideoController;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
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
use Carbon\Carbon;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\GenreRepositoryInterface;
use Core\UseCase\Interfaces\FileStorageInterface;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Core\UseCase\Video\DeleteById\DeleteByIdVideoUseCase;
use Core\UseCase\Video\FindById\FindByIdVideoUseCase;
use Core\UseCase\Video\Insert\InsertVideoUseCase;
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;
use Core\UseCase\Video\Paginate\PaginateVideoUseCase;
use Core\UseCase\Video\Update\UpdateVideoUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\Stubs\VideoEventManagerStub;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class VideoControllerFeatureTest extends TestCase
{

    // atributos
    protected VideoEloquentRepository $repository;
    protected TransactionDbInterface $transactionDb;
    protected FileStorageInterface $fileStorage;
    protected VideoEventManagerInterface $eventManager;
    protected CategoryRepositoryInterface $categoryRepository;
    protected GenreRepositoryInterface $genreRepository;
    protected CastMemberRepositoryInterface $castMemberRepository;

    // sobrescrevendo a função de preparação da classe mãe
    // é executada antes dos testes
    protected function setUp(): void
    {
        // reutilizando as instruções da classe mãe
        parent::setUp();

        // instanciando o repository
        $this->repository = new VideoEloquentRepository(new VideoModel());

        // instanciando o gerenciador de transações
        $this->transactionDb = new TransactionDb();

        // instanciando o gerenciador de storage
        $this->fileStorage = new FileStorage();

        // instanciando o gerenciador de eventos
        $this->eventManager = new VideoEventManagerStub();

        // instanciando o repository de Category
        $this->categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // instanciando o repository de Genre
        $this->genreRepository = new GenreEloquentRepository(new GenreModel());

        // instanciando o repository de CastMember
        $this->castMemberRepository = new CastMemberEloquentRepository(new CastMemberModel());
    }

    // testando o método index
    public function testIndex()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        VideoModel::factory()->count($qtd)->create();

        // instanciando o usecase
        $usecase = new PaginateVideoUseCase($this->repository);

        // instanciando o controller
        $controller = new VideoController();
        // configurando o request
        $request = new Request();
        $request->headers->set('content-type', 'application/json');
        $request->request->add(['per_page' => 10]);
        // executando o index
        $response = $controller->index($request, $usecase);

        // verificando os dados
        $this->assertInstanceOf(AnonymousResourceCollection::class, $response);
        $this->assertArrayHasKey('meta', $response->additional);
    }

    // testando o método show
    public function testShow()
    {
        // inserindo um registro no bd
        $genre = VideoModel::factory()->create();

        // instanciando o usecase
        $usecase = new FindByIdVideoUseCase($this->repository);

        // instanciando o controller
        $controller = new VideoController();

        // executando o show
        $response = $controller->show($genre->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
    }

    // testando o método store
    public function testStore()
    {
        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // instanciando o usecase
        $usecase = new InsertVideoUseCase(
            $this->repository,
            $this->transactionDb,
            $this->fileStorage,
            $this->eventManager,
            $this->categoryRepository,
            $this->genreRepository,
            $this->castMemberRepository
        );

        // instanciando o controller
        $controller = new VideoController();
        // configurando o request com validação específica
        $storeRequest = new StoreVideoRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->setJson(new ParameterBag([
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());

        $this->assertDatabaseHas('videos', [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]);
    }

    // testando o método store
    public function testStoreAll()
    {
        // fakeando o listener o evento que será disparado no armazenameno do videoFile
        Event::fake([
            VideoEventManagerStub::class,
        ]);

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de categorias
        $nCategories = rand(1, 9);
        // criando categorias no bd para possibilitar os relacionamentos
        $categoriesIds = CategoryModel::factory()->count($nCategories)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('categories', $nCategories);
        // 
        // definindo número randomico de genres
        $nGenres = rand(1, 9);
        // criando genres no bd para possibilitar os relacionamentos
        $genresIds = GenreModel::factory()->count($nGenres)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('genres', $nGenres);
        // 
        // definindo número randomico de castMembers
        $nCastMembers = rand(1, 9);
        // criando castMembers no bd para possibilitar os relacionamentos
        $castMembersIds = CastMemberModel::factory()->count($nCastMembers)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('cast_members', $nCastMembers);

        // dados do thumbFile
        $thumbFile = UploadedFile::fake()->image('thumbFile.png');

        // dados do thumbHalf
        $thumbHalf = UploadedFile::fake()->image('thumbHalf.png');

        // dados do bannerFile
        $bannerFile = UploadedFile::fake()->image('bannerFile.png');

        // dados do trailerFile
        $trailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');

        // dados do videoFile
        $videoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'video/mp4');

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // instanciando o usecase
        $usecase = new InsertVideoUseCase(
            $this->repository,
            $this->transactionDb,
            $this->fileStorage,
            $this->eventManager,
            $this->categoryRepository,
            $this->genreRepository,
            $this->castMemberRepository
        );

        // instanciando o controller
        $controller = new VideoController();
        // configurando o request com validação específica
        $storeRequest = new StoreVideoRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->files->add([
            'thumbfile' => $thumbFile,
            'thumbhalf' => $thumbHalf,
            'bannerfile' => $bannerFile,
            'trailerfile' => $trailerFile,
            'videofile' => $videoFile,
        ]);
        $storeRequest->setJson(new ParameterBag([
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
            'categories_id' => $categoriesIds,
            'genres_id' => $genresIds,
            'cast_members_id' => $castMembersIds,            
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());
        $this->assertNotEmpty($response->getData()->data->id);
        $this->assertSame($title, $response->getData()->data->title);
        $this->assertSame($description, $response->getData()->data->description);
        $this->assertSame($yearLaunched, $response->getData()->data->year_launched);
        $this->assertSame($duration, $response->getData()->data->duration);
        $this->assertSame($rating->value, $response->getData()->data->rating);
        $this->assertSame($opened, $response->getData()->data->opened);
        $this->assertNotEmpty($response->getData()->data->created_at);
        $this->assertNotEmpty($response->getData()->data->updated_at);

        $this->assertDatabaseHas('videos', [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);
        $this->assertCount($nCategories, $response->getData()->data->categories_id);
        $this->assertCount($nGenres, $response->getData()->data->genres_id);
        $this->assertCount($nCastMembers, $response->getData()->data->cast_members_id);
        $this->assertEquals($categoriesIds, $response->getData()->data->categories_id);
        $this->assertEquals($genresIds, $response->getData()->data->genres_id);
        $this->assertEquals($castMembersIds, $response->getData()->data->cast_members_id);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $response->getData()->data->id,
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->videos);
        }
        // verificando o relacionamento a partir de genre
        foreach ($genresIds as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $response->getData()->data->id,
                'genre_id' => $genreId,
            ]);
            $genreModel = GenreModel::find($genreId);
            $this->assertCount(1, $genreModel->videos);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($castMembersIds as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $response->getData()->data->id,
                'cast_member_id' => $castMemberId,
            ]);
            $castMemberModel = CastMemberModel::find($castMemberId);
            $this->assertCount(1, $castMemberModel->videos);
        }

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 3);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response->getData()->data->id,
            'path' => $response->getData()->data->thumbfile,
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response->getData()->data->id,
            'path' => $response->getData()->data->thumbhalf,
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response->getData()->data->id,
            'path' => $response->getData()->data->bannerfile,
        ]);

        // verificando se os arquivos de media foram registrados no bd
        $this->assertDatabaseCount('video_medias', 2);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $response->getData()->data->id,
            'file_path' => $response->getData()->data->trailerfile,
        ]);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $response->getData()->data->id,
            'file_path' => $response->getData()->data->videofile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($response->getData()->data->thumbfile);
        Storage::assertExists($response->getData()->data->thumbhalf);
        Storage::assertExists($response->getData()->data->bannerfile);
        Storage::assertExists($response->getData()->data->trailerfile);
        Storage::assertExists($response->getData()->data->videofile);

        // verificando que o evento de armazenamento do videoFile foi disparado
        Event::assertDispatched(VideoEventManagerStub::class);

        // apagando a pasta com os arquivos criados
        Storage::deleteDirectory($response->getData()->data->id);
    }

    // testando o método update
    public function testUpdate()
    {
        // inserindo um registro no bd
        $video = VideoModel::factory()->create();

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // instanciando o usecase
        $usecase = new UpdateVideoUseCase(
            $this->repository,
            $this->transactionDb,
            $this->fileStorage,
            $this->eventManager,
            $this->categoryRepository,
            $this->genreRepository,
            $this->castMemberRepository
        );

        // instanciando o controller
        $controller = new VideoController();
        // configurando o request com validação específica
        $updateRequest = new UpdateVideoRequest();
        $updateRequest->headers->set('content-type', 'application/json');
        $updateRequest->setJson(new ParameterBag([
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]));

        // executando o update
        $response = $controller->update($video->id, $updateRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());

        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]);
    }

    // testando o método update
    public function testUpdateAll()
    {
        // fakeando o listener o evento que será disparado no armazenameno do videoFile
        Event::fake([
            VideoEventManagerStub::class,
        ]);

        // inserindo um registro no bd
        $video = VideoModel::factory()->create();

        // contadores relativos às tabelas associadas
        $categoriesCount = 0;
        $genresCount = 0;
        $castMembersCount = 0;

        // variáveis relacionadas aos arquivos obsoletos
        $thumbfileOld = '';
        $thumbhalfOld = '';
        $bannerfileOld = '';
        $trailerfileOld = '';
        $videofileOld = '';

        // realizando a atualização duas vezes
        for ($i = 0; $i < 2; $i++) {

            // gerando massa de dados a serem utilizados nos relacionamentos
            // definindo número randomico de categorias
            $nCategories = rand(1, 9);
            // criando categorias no bd para possibilitar os relacionamentos
            $categoriesIds = CategoryModel::factory()->count($nCategories)->create()->pluck('id')->toArray();
            $categoriesCount += $nCategories;
            $this->assertDatabaseCount('categories', $categoriesCount);
            // 
            // definindo número randomico de genres
            $nGenres = rand(1, 9);
            // criando genres no bd para possibilitar os relacionamentos
            $genresIds = GenreModel::factory()->count($nGenres)->create()->pluck('id')->toArray();
            $genresCount += $nGenres;
            $this->assertDatabaseCount('genres', $genresCount);
            // 
            // definindo número randomico de castMembers
            $nCastMembers = rand(1, 9);
            // criando castMembers no bd para possibilitar os relacionamentos
            $castMembersIds = CastMemberModel::factory()->count($nCastMembers)->create()->pluck('id')->toArray();
            $castMembersCount += $nCastMembers;
            $this->assertDatabaseCount('cast_members', $castMembersCount);

            // dados do thumbFile
            $thumbFile = UploadedFile::fake()->image('thumbFile.png');

            // dados do thumbHalf
            $thumbHalf = UploadedFile::fake()->image('thumbHalf.png');

            // dados do bannerFile
            $bannerFile = UploadedFile::fake()->image('bannerFile.png');

            // dados do trailerFile
            $trailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');

            // dados do videoFile
            $videoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'video/mp4');

            // dados básicos de entrada
            $title = 'title';
            $description = 'description';
            $yearLaunched = 2024;
            $duration = 180;
            $opened = false;
            $rating = Rating::RATE10;

            // instanciando o usecase
            $usecase = new UpdateVideoUseCase(
                $this->repository,
                $this->transactionDb,
                $this->fileStorage,
                $this->eventManager,
                $this->categoryRepository,
                $this->genreRepository,
                $this->castMemberRepository
            );

            // instanciando o controller
            $controller = new VideoController();
            // configurando o request com validação específica
            $updateRequest = new UpdateVideoRequest();
            $updateRequest->headers->set('content-type', 'application/json');
            $updateRequest->files->add([
                'thumbfile' => $thumbFile,
                'thumbhalf' => $thumbHalf,
                'bannerfile' => $bannerFile,
                'trailerfile' => $trailerFile,
                'videofile' => $videoFile,
            ]);
            $updateRequest->setJson(new ParameterBag([
                'title' => $title,
                'description' => $description,
                'year_launched' => $yearLaunched,
                'duration' => $duration,
                'rating' => $rating,
                'opened' => $opened,
                'categories_id' => $categoriesIds,
                'genres_id' => $genresIds,
                'cast_members_id' => $castMembersIds,                
            ]));

            // executando o update
            sleep(1);
            $response = $controller->update($video->id, $updateRequest, $usecase);

            // verificando os dados
            $this->assertInstanceOf(JsonResponse::class, $response);
            $this->assertSame(Response::HTTP_OK, $response->status());

            $this->assertSame($video->id, $response->getData()->data->id);
            $this->assertSame($title, $response->getData()->data->title);
            $this->assertSame($description, $response->getData()->data->description);
            $this->assertSame($yearLaunched, $response->getData()->data->year_launched);
            $this->assertSame($duration, $response->getData()->data->duration);
            $this->assertSame($rating->value, $response->getData()->data->rating);
            $this->assertSame($opened, $response->getData()->data->opened);
            $this->assertSame(Carbon::make($video->created_at)->format('Y-m-d H:i:s'), $response->getData()->data->created_at);
            $this->assertNotSame(Carbon::make($video->updated_at)->format('Y-m-d H:i:s'), $response->getData()->data->updated_at);
            $this->assertNotSame($response->getData()->data->created_at, $response->getData()->data->updated_at);

            $this->assertDatabaseHas('videos', [
                'id' => $video->id,
                'title' => $title,
                'description' => $description,
                'year_launched' => $yearLaunched,
                'duration' => $duration,
                'rating' => $rating,
                'opened' => $opened,
            ]);

            // verificando relacionamentos
            $this->assertDatabaseCount('video_category', $nCategories);
            $this->assertDatabaseCount('video_genre', $nGenres);
            $this->assertDatabaseCount('video_cast_member', $nCastMembers);
            $this->assertCount($nCategories, $response->getData()->data->categories_id);
            $this->assertCount($nGenres, $response->getData()->data->genres_id);
            $this->assertCount($nCastMembers, $response->getData()->data->cast_members_id);
            $this->assertEquals($categoriesIds, $response->getData()->data->categories_id);
            $this->assertEquals($genresIds, $response->getData()->data->genres_id);
            $this->assertEquals($castMembersIds, $response->getData()->data->cast_members_id);

            // verificando o relacionamento a partir de category
            foreach ($categoriesIds as $categoryId) {
                $this->assertDatabaseHas('video_category', [
                    'video_id' => $response->getData()->data->id,
                    'category_id' => $categoryId,
                ]);
                $categoryModel = CategoryModel::find($categoryId);
                $this->assertCount(1, $categoryModel->videos);
            }
            // verificando o relacionamento a partir de genre
            foreach ($genresIds as $genreId) {
                $this->assertDatabaseHas('video_genre', [
                    'video_id' => $response->getData()->data->id,
                    'genre_id' => $genreId,
                ]);
                $genreModel = GenreModel::find($genreId);
                $this->assertCount(1, $genreModel->videos);
            }
            // verificando o relacionamento a partir de castMember
            foreach ($castMembersIds as $castMemberId) {
                $this->assertDatabaseHas('video_cast_member', [
                    'video_id' => $response->getData()->data->id,
                    'cast_member_id' => $castMemberId,
                ]);
                $castMemberModel = CastMemberModel::find($castMemberId);
                $this->assertCount(1, $castMemberModel->videos);
            }

            // verificando se os arquivos de image foram registrados no bd
            $this->assertDatabaseCount('video_images', 3);
            $this->assertDatabaseHas('video_images', [
                'video_id' => $response->getData()->data->id,
                'path' => $response->getData()->data->thumbfile,
            ]);
            $this->assertDatabaseHas('video_images', [
                'video_id' => $response->getData()->data->id,
                'path' => $response->getData()->data->thumbhalf,
            ]);
            $this->assertDatabaseHas('video_images', [
                'video_id' => $response->getData()->data->id,
                'path' => $response->getData()->data->bannerfile,
            ]);

            // verificando se os arquivos de media foram registrados no bd
            $this->assertDatabaseCount('video_medias', 2);
            $this->assertDatabaseHas('video_medias', [
                'video_id' => $response->getData()->data->id,
                'file_path' => $response->getData()->data->trailerfile,
            ]);
            $this->assertDatabaseHas('video_medias', [
                'video_id' => $response->getData()->data->id,
                'file_path' => $response->getData()->data->videofile,
            ]);

            // verificando se os arquivos foram armazenados
            Storage::assertExists($response->getData()->data->thumbfile);
            Storage::assertExists($response->getData()->data->thumbhalf);
            Storage::assertExists($response->getData()->data->bannerfile);
            Storage::assertExists($response->getData()->data->trailerfile);
            Storage::assertExists($response->getData()->data->videofile);

            // verificando se os arquivos obsoletos foram apagados
            if ($thumbfileOld) Storage::assertMissing($thumbfileOld);
            if ($thumbhalfOld) Storage::assertMissing($thumbhalfOld);
            if ($bannerfileOld) Storage::assertMissing($bannerfileOld);
            if ($trailerfileOld) Storage::assertMissing($trailerfileOld);
            if ($videofileOld) Storage::assertMissing($videofileOld);

            // armazenando os paths dos arquivos obsoletos
            $thumbfileOld = $response->getData()->data->thumbfile;
            $thumbhalfOld = $response->getData()->data->thumbhalf;
            $bannerfileOld = $response->getData()->data->bannerfile;
            $trailerfileOld = $response->getData()->data->trailerfile;
            $videofileOld = $response->getData()->data->videofile;

            // verificando que o evento de armazenamento do videoFile foi disparado
            Event::assertDispatched(VideoEventManagerStub::class);
        }

        // executando atualização de remoção de relacionamentos e arquivos
        $updateRequest = new UpdateVideoRequest();
        $updateRequest->headers->set('content-type', 'application/json');
        $updateRequest->files->add([
            'thumbfile' => [],
            'thumbhalf' => [],
            'bannerfile' => [],
            'trailerfile' => [],
            'videofile' => [],
        ]);
        $updateRequest->setJson(new ParameterBag([
            'categories_id' => [],
            'genres_id' => [],
            'cast_members_id' => [],                
        ]));
        
        // fazendo o request
        sleep(1);
        $response = $controller->update($video->id, $updateRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());

        $this->assertSame($video->id, $response->getData()->data->id);
        $this->assertSame($title, $response->getData()->data->title);
        $this->assertSame($description, $response->getData()->data->description);
        $this->assertSame($yearLaunched, $response->getData()->data->year_launched);
        $this->assertSame($duration, $response->getData()->data->duration);
        $this->assertSame($rating->value, $response->getData()->data->rating);
        $this->assertSame($opened, $response->getData()->data->opened);
        $this->assertSame(Carbon::make($video->created_at)->format('Y-m-d H:i:s'), $response->getData()->data->created_at);
        $this->assertNotSame(Carbon::make($video->updated_at)->format('Y-m-d H:i:s'), $response->getData()->data->updated_at);
        $this->assertNotSame($response->getData()->data->created_at, $response->getData()->data->updated_at);

        $this->assertDatabaseHas('videos', [
            'id' => $video->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_category', 0);
        $this->assertDatabaseCount('video_genre', 0);
        $this->assertDatabaseCount('video_cast_member', 0);
        $this->assertCount(0, $response->getData()->data->categories_id);
        $this->assertCount(0, $response->getData()->data->genres_id);
        $this->assertCount(0, $response->getData()->data->cast_members_id);
        $this->assertEquals([], $response->getData()->data->categories_id);
        $this->assertEquals([], $response->getData()->data->genres_id);
        $this->assertEquals([], $response->getData()->data->cast_members_id);

        // verificando se os arquivos de image foram removidos no bd
        $this->assertDatabaseCount('video_images', 0);
        
        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_medias', 0);
        
        // confirmando que os arquivos foram removidos do storage
        Storage::assertDirectoryEmpty($response->getData()->data->id);

        // apagando a pasta de arquivos criada
        Storage::deleteDirectory($response->getData()->data->id);
    }

    // testando o método destroy
    public function testDestroy()
    {
        // inserindo um registro no bd
        $video = VideoModel::factory()->create();

        // instanciando o usecase
        $usecase = new DeleteByIdVideoUseCase($this->repository);

        // instanciando o controller
        $controller = new VideoController();

        // executando o destroy
        $response = $controller->destroy($video->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->status());

        $this->assertSoftDeleted('videos', [
            'id' => $video->id
        ]);
    }
}
