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
use Core\Domain\Enum\Rating;
use Core\Domain\Exception\NotFoundException;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\DTO\UpdateVideoOutputDto;
use Core\UseCase\Video\Update\UpdateVideoUseCase;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\VideoEventManagerStub;
use Tests\TestCase;

class UpdateVideoUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução sem relacionamentos
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);
    }

    // função que testa o método de execução com categorias
    public function testExecuteWithCategories()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de categorias
        $nCategories = rand(1, 9);
        // criando categorias no bd para possibilitar os relacionamentos
        $categoriesIds = CategoryModel::factory()->count($nCategories)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('categories', $nCategories);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesIds,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertCount($nCategories, $responseUseCase->categoriesId);
        $this->assertEquals($categoriesIds, $responseUseCase->categoriesId);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $responseUseCase->id,
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->videos);
        }
    }

    // função que testa o método de execução com categorias e rollback
    public function testExecuteWithCategoriesAndRollback()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de categorias
        $nCategories = rand(1, 9);
        // criando categorias no bd para possibilitar os relacionamentos
        $categoriesIds = CategoryModel::factory()->count($nCategories)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('categories', $nCategories);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto, true);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame(explode(':', $th->getMessage())[0], 'rollback test id');
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_category', 0);
        }
    }

    // função que testa o método de execução com categorias inválidas
    public function testExecuteWithInvalidCategories()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        $categoryId = 'fake';
        $categoriesIds = [$categoryId];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), "Category $categoryId not found");
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_category', 0);
        }
    }

    // função que testa o método de execução com genres
    public function testExecuteWithGenres()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de genres
        $nGenres = rand(1, 9);
        // criando genres no bd para possibilitar os relacionamentos
        $genresIds = GenreModel::factory()->count($nGenres)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('genres', $nGenres);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            genresId: $genresIds,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertCount($nGenres, $responseUseCase->genresId);
        $this->assertEquals($genresIds, $responseUseCase->genresId);

        // verificando o relacionamento a partir de genre
        foreach ($genresIds as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $responseUseCase->id,
                'genre_id' => $genreId,
            ]);
            $genreModel = GenreModel::find($genreId);
            $this->assertCount(1, $genreModel->videos);
        }
    }

    // função que testa o método de execução com genres e rollback
    public function testExecuteWithGenresAndRollback()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de genres
        $nGenres = rand(1, 9);
        // criando genres no bd para possibilitar os relacionamentos
        $genresIds = GenreModel::factory()->count($nGenres)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('genres', $nGenres);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            genresId: $genresIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto, true);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame(explode(':', $th->getMessage())[0], 'rollback test id');
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_genre', 0);
        }
    }

    // função que testa o método de execução com genres inválidos
    public function testExecuteWithInvalidGenres()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        $genreId = 'fake';
        $genresIds = [$genreId];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            genresId: $genresIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), "Genre $genreId not found");
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_genre', 0);
        }
    }

    // função que testa o método de execução com castMembers
    public function testExecuteWithCastMembers()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de castMembers
        $nCastMembers = rand(1, 9);
        // criando castMembers no bd para possibilitar os relacionamentos
        $castMembersIds = CastMemberModel::factory()->count($nCastMembers)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('cast_members', $nCastMembers);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            castMembersId: $castMembersIds,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);
        $this->assertCount($nCastMembers, $responseUseCase->castMembersId);
        $this->assertEquals($castMembersIds, $responseUseCase->castMembersId);

        // verificando o relacionamento a partir de castMember
        foreach ($castMembersIds as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $responseUseCase->id,
                'cast_member_id' => $castMemberId,
            ]);
            $castMemberModel = CastMemberModel::find($castMemberId);
            $this->assertCount(1, $castMemberModel->videos);
        }
    }

    // função que testa o método de execução com castMembers e rollback
    public function testExecuteWithCastMembersAndRollback()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        // definindo número randomico de castMembers
        $nCastMembers = rand(1, 9);
        // criando castMembers no bd para possibilitar os relacionamentos
        $castMembersIds = CastMemberModel::factory()->count($nCastMembers)->create()->pluck('id')->toArray();
        $this->assertDatabaseCount('cast_members', $nCastMembers);

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            castMembersId: $castMembersIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto, true);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame(explode(':', $th->getMessage())[0], 'rollback test id');
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_cast_member', 0);
        }
    }

    // função que testa o método de execução com castMembers inválidos
    public function testExecuteWithInvalidCastMembers()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // gerando massa de dados a serem utilizados nos relacionamentos
        $castMemberId = 'fake';
        $castMembersIds = [$castMemberId];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            castMembersId: $castMembersIds,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), "Cast Member $castMemberId not found");
            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_cast_member', 0);
        }
    }

    // função que testa o método de execução com trailerFile
    public function testExecuteWithTrailerFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            trailerFile: $trailerFile,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // verificando se os arquivos de media foram registrados no bd
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $responseUseCase->id,
            'file_path' => $responseUseCase->trailerFile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->trailerFile);

        // apagando o arquivo armazenado        
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com remoção do trailerFile
    public function testExecuteWithRemovingTrailerFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            trailerFile: $trailerFile,
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

        // verificando registro
        $this->assertDatabaseCount('video_medias', 1);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->trailerFile);

        // removendo o arquivo
        sleep(1);
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            trailerFile: [],
        );

        // executando o usecase
        $useCase->execute($inputDto);

        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_medias', 0);

        // verificando se os arquivos foram removidos
        Storage::assertMissing($responseUseCase->trailerFile);

        // apagando o arquivo armazenado        
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com videoFile
    public function testExecuteWithVideoFile()
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // verificando se os arquivos de media foram registrados no bd
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $responseUseCase->id,
            'file_path' => $responseUseCase->videoFile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->videoFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com remoção do videoFile
    public function testExecuteWithRemovingVideoFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do videoFile
        $fakeVideoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'video/mp4');
        $videoFile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            videoFile: $videoFile,
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

        // verificando registro
        $this->assertDatabaseCount('video_medias', 1);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->videoFile);

        // removendo o arquivo
        sleep(1);
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            videoFile: [],
        );

        // executando o usecase
        $useCase->execute($inputDto);

        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_medias', 0);

        // verificando se os arquivos foram removidos
        Storage::assertMissing($responseUseCase->videoFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com thumbFile
    public function testExecuteWithThumbFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do thumbFile
        $fakeThumbFile = UploadedFile::fake()->create('thumbfile.png', 1, 'image/png');
        $thumbfile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbFile: $thumbfile,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->thumbFile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com remoção do thumbFile
    public function testExecuteWithRemovingThumbFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do thumbFile
        $fakeThumbFile = UploadedFile::fake()->create('thumbfile.png', 1, 'image/png');
        $thumbfile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbFile: $thumbfile,
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

        // verificando registro
        $this->assertDatabaseCount('video_images', 1);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbFile);

        // removendo o arquivo
        sleep(1);
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbFile: [],
        );

        // executando o usecase
        $useCase->execute($inputDto);

        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_images', 0);

        // verificando se os arquivos foram removidos
        Storage::assertMissing($responseUseCase->thumbFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com thumbHalf
    public function testExecuteWithThumbHalf()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbhalf.png', 1, 'image/png');
        $thumbhalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbHalf: $thumbhalf,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->thumbHalf,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbHalf);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com remoção do thumbHalf
    public function testExecuteWithRemovingThumbHalf()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbhalf.png', 1, 'image/png');
        $thumbhalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbHalf: $thumbhalf,
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

        // verificando registro
        $this->assertDatabaseCount('video_images', 1);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbHalf);

        // removendo o arquivo
        sleep(1);
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            thumbHalf: [],
        );

        // executando o usecase
        $useCase->execute($inputDto);

        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_images', 0);

        // verificando se os arquivos foram removidos
        Storage::assertMissing($responseUseCase->thumbHalf);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com bannerFile
    public function testExecuteWithBannerFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerfile.png', 1, 'image/png');
        $bannerfile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            bannerFile: $bannerfile,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->bannerFile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->bannerFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução com remoção do bannerFile
    public function testExecuteWithRemovingBannerFile()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerfile.png', 1, 'image/png');
        $bannerfile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            bannerFile: $bannerfile,
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

        // verificando registro
        $this->assertDatabaseCount('video_images', 1);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->bannerFile);

        // removendo o arquivo
        sleep(1);
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            bannerFile: [],
        );

        // executando o usecase
        $useCase->execute($inputDto);

        // verificando se os arquivos de media foram removidos no bd
        $this->assertDatabaseCount('video_images', 0);

        // verificando se os arquivos foram removidos
        Storage::assertMissing($responseUseCase->bannerFile);

        // apagando o arquivo armazenado
        Storage::deleteDirectory($model->id);
    }

    // função que testa o método de execução completo
    public function testExecuteAll()
    {
        // fakeando o listener o evento que será disparado no armazenameno do videoFile
        Event::fake([
            VideoEventManagerStub::class,
        ]);

        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

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
        $fakeThumbFile = UploadedFile::fake()->create('thumbFile.png', 1, 'image/png');
        $thumbFile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbHalf.png', 1, 'image/png');
        $thumbHalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerFile.png', 1, 'image/png');
        $bannerFile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // dados do videoFile
        $fakeVideoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'video/mp4');
        $videoFile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesIds,
            genresId: $genresIds,
            castMembersId: $castMembersIds,
            thumbFile: $thumbFile,
            thumbHalf: $thumbHalf,
            bannerFile: $bannerFile,
            trailerFile: $trailerFile,
            videoFile: $videoFile,
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

        // verificando os dados básicos
        $this->assertInstanceOf(UpdateVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);
        $this->assertDatabaseHas('videos', [
            'id' => $responseUseCase->id,
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);
        $this->assertCount($nCategories, $responseUseCase->categoriesId);
        $this->assertCount($nGenres, $responseUseCase->genresId);
        $this->assertCount($nCastMembers, $responseUseCase->castMembersId);
        $this->assertEquals($categoriesIds, $responseUseCase->categoriesId);
        $this->assertEquals($genresIds, $responseUseCase->genresId);
        $this->assertEquals($castMembersIds, $responseUseCase->castMembersId);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $responseUseCase->id,
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->videos);
        }
        // verificando o relacionamento a partir de genre
        foreach ($genresIds as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $responseUseCase->id,
                'genre_id' => $genreId,
            ]);
            $genreModel = GenreModel::find($genreId);
            $this->assertCount(1, $genreModel->videos);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($castMembersIds as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $responseUseCase->id,
                'cast_member_id' => $castMemberId,
            ]);
            $castMemberModel = CastMemberModel::find($castMemberId);
            $this->assertCount(1, $castMemberModel->videos);
        }

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 3);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->thumbFile,
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->thumbHalf,
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $responseUseCase->id,
            'path' => $responseUseCase->bannerFile,
        ]);

        // verificando se os arquivos de media foram registrados no bd
        $this->assertDatabaseCount('video_medias', 2);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $responseUseCase->id,
            'file_path' => $responseUseCase->trailerFile,
        ]);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $responseUseCase->id,
            'file_path' => $responseUseCase->videoFile,
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbFile);
        Storage::assertExists($responseUseCase->thumbHalf);
        Storage::assertExists($responseUseCase->bannerFile);
        Storage::assertExists($responseUseCase->trailerFile);
        Storage::assertExists($responseUseCase->videoFile);

        // verificando que o evento de armazenamento do videoFile foi disparado
        Event::assertDispatched(VideoEventManagerStub::class);

        // apagando os arquivos armazenados
        Storage::deleteDirectory($responseUseCase->id);
    }

    // função que testa o método de execução completo e rollback
    public function testExecuteAllAndRollback()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        sleep(1);

        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

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
        $fakeThumbFile = UploadedFile::fake()->create('thumbFile.png', 1, 'image/png');
        $thumbFile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbHalf.png', 1, 'image/png');
        $thumbHalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerFile.png', 1, 'image/png');
        $bannerFile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'video/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // dados do videoFile
        $fakeVideoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'video/mp4');
        $videoFile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesIds,
            genresId: $genresIds,
            castMembersId: $castMembersIds,
            thumbFile: $thumbFile,
            thumbHalf: $thumbHalf,
            bannerFile: $bannerFile,
            trailerFile: $trailerFile,
            videoFile: $videoFile,
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

        // tratamento de exceções
        try {
            // executando o usecase
            $responseUseCase = $useCase->execute($inputDto, true);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame(explode(':', $th->getMessage())[0], 'rollback test id');

            // verificando as tabelas do banco
            $this->assertDatabaseCount('videos', 1);
            $this->assertDatabaseCount('video_category', 0);
            $this->assertDatabaseCount('video_genre', 0);
            $this->assertDatabaseCount('video_cast_member', 0);
            $this->assertDatabaseCount('video_images', 0);
            $this->assertDatabaseCount('video_medias', 0);

            // verificando se os arquivos não foram armazenados no storage
            Storage::assertDirectoryEmpty(explode(':', $th->getMessage())[1]);

            // apagando os arquivos criados
            Storage::deleteDirectory(explode(':', $th->getMessage())[1]);
        }
    }
}
