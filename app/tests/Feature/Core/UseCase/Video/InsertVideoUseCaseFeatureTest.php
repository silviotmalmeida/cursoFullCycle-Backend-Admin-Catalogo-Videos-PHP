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
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
use Core\UseCase\Video\Insert\InsertVideoUseCase;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\VideoEventManagerStub;
use Tests\TestCase;

class InsertVideoUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução sem relacionamentos
    public function testExecute()
    {
        // dados básicos de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // criando o inputDto
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
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
    }

    // função que testa o método de execução com categorias
    public function testExecuteWithCategories()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_category', 0);
        }
    }

    // função que testa o método de execução com categorias inválidas
    public function testExecuteWithInvalidCategories()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_category', 0);
        }
    }

    // função que testa o método de execução com genres
    public function testExecuteWithGenres()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_genre', 0);
        }
    }

    // função que testa o método de execução com genres inválidos
    public function testExecuteWithInvalidGenres()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_genre', 0);
        }
    }

    // função que testa o método de execução com castMembers
    public function testExecuteWithCastMembers()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_cast_member', 0);
        }
    }

    // função que testa o método de execução com castMembers inválidos
    public function testExecuteWithInvalidCastMembers()
    {
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
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
            $this->assertDatabaseCount('video_cast_member', 0);
        }
    }

    // função que testa o método de execução completo
    public function testExecuteAll()
    {
        // fakeando o listener o evento que será disparado no armazenameno do videoFile
        Event::fake([
            VideoEventManagerStub::class,
        ]);

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
        $fakeThumbFile = UploadedFile::fake()->create('thumbFile.png', 1, 'thumbFile/png');
        $thumbFile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbHalf.png', 1, 'thumbHalf/png');
        $thumbHalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerFile.png', 1, 'bannerFile/png');
        $bannerFile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'trailerFile/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // dados do videoFile
        $fakeVideoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'videoFile/mp4');
        $videoFile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertSame($responseUseCase->created_at, $responseUseCase->updated_at);
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

        // verificando se os arquivos foram armazenados
        Storage::assertExists($responseUseCase->thumbFile);
        Storage::assertExists($responseUseCase->thumbHalf);
        Storage::assertExists($responseUseCase->bannerFile);
        Storage::assertExists($responseUseCase->trailerFile);
        Storage::assertExists($responseUseCase->videoFile);

        // verificando que o evento de armazenamento do videoFile foi disparado
        Event::assertDispatched(VideoEventManagerStub::class);

        // apagando a pasta com os arquivos criados
        Storage::deleteDirectory($responseUseCase->id);
    }

    // função que testa o método de execução completo e rollback
    public function testExecuteAllAndRollback()
    {
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
        $fakeThumbFile = UploadedFile::fake()->create('thumbFile.png', 1, 'thumbFile/png');
        $thumbFile = [
            'name' => $fakeThumbFile->getFilename(),
            'type' => $fakeThumbFile->getMimeType(),
            'tmp_name' => $fakeThumbFile->getPathname(),
            'error' => $fakeThumbFile->getError(),
            'size' => $fakeThumbFile->getSize(),
        ];

        // dados do thumbHalf
        $fakeThumbHalf = UploadedFile::fake()->create('thumbHalf.png', 1, 'thumbHalf/png');
        $thumbHalf = [
            'name' => $fakeThumbHalf->getFilename(),
            'type' => $fakeThumbHalf->getMimeType(),
            'tmp_name' => $fakeThumbHalf->getPathname(),
            'error' => $fakeThumbHalf->getError(),
            'size' => $fakeThumbHalf->getSize(),
        ];

        // dados do bannerFile
        $fakeBannerFile = UploadedFile::fake()->create('bannerFile.png', 1, 'bannerFile/png');
        $bannerFile = [
            'name' => $fakeBannerFile->getFilename(),
            'type' => $fakeBannerFile->getMimeType(),
            'tmp_name' => $fakeBannerFile->getPathname(),
            'error' => $fakeBannerFile->getError(),
            'size' => $fakeBannerFile->getSize(),
        ];

        // dados do trailerFile
        $fakeTrailerFile = UploadedFile::fake()->create('trailerFile.mp4', 1, 'trailerFile/mp4');
        $trailerFile = [
            'name' => $fakeTrailerFile->getFilename(),
            'type' => $fakeTrailerFile->getMimeType(),
            'tmp_name' => $fakeTrailerFile->getPathname(),
            'error' => $fakeTrailerFile->getError(),
            'size' => $fakeTrailerFile->getSize(),
        ];

        // dados do videoFile
        $fakeVideoFile = UploadedFile::fake()->create('videoFile.mp4', 1, 'videoFile/mp4');
        $videoFile = [
            'name' => $fakeVideoFile->getFilename(),
            'type' => $fakeVideoFile->getMimeType(),
            'tmp_name' => $fakeVideoFile->getPathname(),
            'error' => $fakeVideoFile->getError(),
            'size' => $fakeVideoFile->getSize(),
        ];

        // criando o inputDto
        $inputDto = new InsertVideoInputDto(
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
        $useCase = new InsertVideoUseCase(
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
            $this->assertDatabaseCount('videos', 0);
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
