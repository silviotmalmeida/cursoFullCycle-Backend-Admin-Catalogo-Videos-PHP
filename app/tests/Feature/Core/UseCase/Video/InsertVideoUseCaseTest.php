<?php

namespace Tests\Feature\Core\UseCase\Video;

use App\Events\VideoEventManager;
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
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\DTO\InsertVideoOutputDto;
use Core\UseCase\Video\Insert\InsertVideoUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InsertVideoUseCaseTest extends TestCase
{
    // função que testa o método de execução sem categorias
    public function testExecute()
    {
        // dados de entrada
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;
        $categoriesId = [];
        $genresId = [];
        $castMembersId = [];
        $thumbFile = null;
        $thumbHalf = null;
        $bannerFile = null;
        $trailerFile = null;
        $videoFile = null;

        // criando o inputDto
        $inputDto = new InsertVideoInputDto(
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            opened: $opened,
            rating: $rating,
            categoriesId: $categoriesId,
            genresId: $genresId,
            castMembersId: $castMembersId,
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
        $eventManager = new VideoEventManager();

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

        // verificando os dados
        $this->assertInstanceOf(InsertVideoOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($title, $responseUseCase->title);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($yearLaunched, $responseUseCase->yearLaunched);
        $this->assertSame($duration, $responseUseCase->duration);
        $this->assertSame($rating, $responseUseCase->rating);
        $this->assertSame($categoriesId, $responseUseCase->categoriesId);
        $this->assertSame($genresId, $responseUseCase->genresId);
        $this->assertSame($castMembersId, $responseUseCase->castMembersId);
        $this->assertSame($thumbFile, $responseUseCase->thumbFile);
        $this->assertSame($thumbHalf, $responseUseCase->thumbHalf);
        $this->assertSame($bannerFile, $responseUseCase->bannerFile);
        $this->assertSame($trailerFile, $responseUseCase->trailerFile);
        $this->assertSame($videoFile, $responseUseCase->videoFile);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        $this->assertDatabaseHas('videos', [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ]);
    }

    // // função que testa o método de execução com categorias
    // public function testExecuteWithCategories()
    // {
    //     // criando as categorias
    //     $qtd = random_int(10, 20);
    //     $categories = CategoryModel::factory()->count($qtd)->create();

    //     // obtendo o array de id das categorias
    //     $categoriesIds = $categories->pluck('id')->toArray();

    //     // dados de entrada
    //     $name = 'name genre';
    //     $isActive = false;

    //     // criando o inputDto
    //     $inputDto = new InsertGenreInputDto(
    //         name: $name,
    //         isActive: $isActive,
    //         categoriesId: $categoriesIds
    //     );

    //     // criando o repository
    //     $repository = new GenreEloquentRepository(new GenreModel());

    //     // criando o gerenciador de transações
    //     $transactionDb = new TransactionDb();

    //     // criando o repository da Category
    //     $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

    //     // criando o usecase
    //     $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);

    //     // executando o usecase
    //     $responseUseCase = $useCase->execute($inputDto);

    //     // verificando os dados
    //     $this->assertInstanceOf(InsertGenreOutputDto::class, $responseUseCase);
    //     $this->assertNotEmpty($responseUseCase->id);
    //     $this->assertSame($name, $responseUseCase->name);
    //     $this->assertSame($isActive, $responseUseCase->is_active);
    //     $this->assertCount($qtd, $responseUseCase->categories_id);
    //     $this->assertNotEmpty($responseUseCase->created_at);
    //     $this->assertNotEmpty($responseUseCase->updated_at);

    //     $this->assertDatabaseHas('genres', [
    //         'name' => $name,
    //         'is_active' => $isActive
    //     ]);

    //     $this->assertDatabaseCount('category_genre', $qtd);
    // }

    // // função que testa o método de execução com categorias e rollback
    // public function testExecuteWithCategoriesAndRollback()
    // {
    //     // criando as categorias
    //     $qtd = random_int(10, 20);
    //     $categories = CategoryModel::factory()->count($qtd)->create();

    //     // obtendo o array de id das categorias
    //     $categoriesIds = $categories->pluck('id')->toArray();

    //     // dados de entrada
    //     $name = 'name genre';
    //     $isActive = false;

    //     // criando o inputDto
    //     $inputDto = new InsertGenreInputDto(
    //         name: $name,
    //         isActive: $isActive,
    //         categoriesId: $categoriesIds
    //     );

    //     // criando o repository
    //     $repository = new GenreEloquentRepository(new GenreModel());

    //     // criando o gerenciador de transações
    //     $transactionDb = new TransactionDb();

    //     // criando o repository da Category
    //     $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

    //     // tratamento de exceções
    //     try {
    //         // criando o usecase
    //         $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);
    //         // executando o usecase
    //         $useCase->execute($inputDto, true);
    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(Exception::class, $th);
    //         $this->assertEquals($th->getMessage(), "rollback test");
    //         $this->assertDatabaseCount('genres', 0);
    //         $this->assertDatabaseCount('category_genre', 0);
    //     }
    // }

    // // função que testa o método de execução com categorias inválidas
    // public function testExecuteWithInvalidCategories()
    // {
    //     try {
    //         // criando o id da categoria
    //         $categoryId = 'fake';

    //         // dados de entrada
    //         $name = 'name genre';
    //         $isActive = false;
    //         $categoriesIds = [$categoryId];

    //         // criando o inputDto
    //         $inputDto = new InsertGenreInputDto(
    //             name: $name,
    //             isActive: $isActive,
    //             categoriesId: $categoriesIds
    //         );

    //         // criando o repository
    //         $repository = new GenreEloquentRepository(new GenreModel());

    //         // criando o gerenciador de transações
    //         $transactionDb = new TransactionDb();

    //         // criando o repository da Category
    //         $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

    //         // criando o usecase
    //         $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);

    //         // executando o usecase
    //         $useCase->execute($inputDto);

    //         // se não lançar exceção o teste deve falhar
    //         $this->assertTrue(false);
    //     } catch (\Throwable $th) {
    //         // verificando o tipo da exceção
    //         $this->assertInstanceOf(NotFoundException::class, $th);
    //         // verificando a mensagem da exceção
    //         $this->assertSame($th->getMessage(), "Category $categoryId not found");
    //     }
    // }
}
