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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InsertVideoUseCaseTest extends TestCase
{
    // função que testa o método de execução sem categorias
    public function testExecute()
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

        // apagando a pasta com os arquivos criados
        Storage::deleteDirectory(explode('/', $responseUseCase->thumbFile)[0]);
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
