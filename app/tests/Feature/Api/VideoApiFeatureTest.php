<?php

namespace Tests\Feature\Api;

use App\Models\CastMember as CastMemberModel;
use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use App\Models\Video as VideoModel;
use Carbon\Carbon;
use Core\Domain\Enum\Rating;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class VideoApiFeatureTest extends TestCase
{
    // atributos
    private $endpoint = '/api/videos';

    // testando o método index com retorno vazio
    public function testIndexWithNoVideos()
    {
        // fazendo o request
        $response = $this->getJson($this->endpoint);

        // validando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'data');
    }

    // testando o método index
    public function testIndex()
    {
        // definindo a quantidade de registros a serem criados
        $total = 50;
        $perPage = 8;
        $lastPage = (int) (ceil($total / $perPage));
        $firstPage = 1;
        $currentPage = 2;
        $to = ($currentPage - 1) * ($perPage) + 1;
        $from = $currentPage * $perPage;

        // inserindo múltiplos registros no bd
        VideoModel::factory()->count($total)->create();

        // fazendo o request
        $response = $this->getJson("$this->endpoint?page=$currentPage&per_page=$perPage");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($perPage, $response['data']);
        $this->assertSame($total, $response['meta']['total']);
        $this->assertSame($perPage, $response['meta']['per_page']);
        $this->assertSame($lastPage, $response['meta']['last_page']);
        $this->assertSame($firstPage, $response['meta']['first_page']);
        $this->assertSame($currentPage, $response['meta']['current_page']);
        $this->assertSame($to, $response['meta']['to']);
        $this->assertSame($from, $response['meta']['from']);
    }

    // testando o método show com id inexistente
    public function testShowNotFound()
    {
        // fazendo o request
        $response = $this->getJson("{$this->endpoint}/fake_id");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    // testando o método show
    public function testShow()
    {
        // inserindo um registro no bd
        $video = VideoModel::factory()->create();

        // fazendo o request
        $response = $this->getJson("{$this->endpoint}/{$video->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'year_launched',
                'duration',
                'rating',
                'opened',
                'categories_id',
                'genres_id',
                'cast_members_id',
                'thumbfile',
                'thumbhalf',
                'bannerfile',
                'trailerfile',
                'videofile',
                'created_at',
                'updated_at',
            ]
        ]);

        $this->assertSame($video->id, $response['data']['id']);
        $this->assertSame($video->title, $response['data']['title']);
        $this->assertSame($video->description, $response['data']['description']);
        $this->assertSame($video->year_launched, $response['data']['year_launched']);
        $this->assertSame($video->duration, $response['data']['duration']);
        $this->assertSame($video->rating, $response['data']['rating']);
        $this->assertSame($video->opened, $response['data']['opened']);
        $this->assertSame(Carbon::make($video->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertSame(Carbon::make($video->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);
    }

    // testando o método store sem passagem dos atributos para criação
    public function testStoreWithoutData()
    {
        // definindo os dados a serem passados no body
        $data = [];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'description',
                'year_launched',
                'duration',
            ]
        ]);
    }

    // testando o método store
    public function testStore()
    {
        // definindo os dados a serem passados no body
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'year_launched',
                'duration',
                'rating',
                'opened',
                'categories_id',
                'genres_id',
                'cast_members_id',
                'thumbfile',
                'thumbhalf',
                'bannerfile',
                'trailerfile',
                'videofile',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($title, $response['data']['title']);
        $this->assertSame($description, $response['data']['description']);
        $this->assertSame($yearLaunched, $response['data']['year_launched']);
        $this->assertSame($duration, $response['data']['duration']);
        $this->assertSame($rating->value, $response['data']['rating']);
        $this->assertSame($opened, $response['data']['opened']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

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

        // definindo os dados a serem passados no body
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'categories_id' => $categoriesIds,
            'genres_id' => $genresIds,
            'cast_members_id' => $castMembersIds,
            'thumbfile' => $thumbFile,
            'thumbhalf' => $thumbHalf,
            'bannerfile' => $bannerFile,
            'trailerfile' => $trailerFile,
            'videofile' => $videoFile,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'year_launched',
                'duration',
                'rating',
                'opened',
                'categories_id',
                'genres_id',
                'cast_members_id',
                'thumbfile',
                'thumbhalf',
                'bannerfile',
                'trailerfile',
                'videofile',
                'created_at',
                'updated_at',
            ]
        ]);

        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($title, $response['data']['title']);
        $this->assertSame($description, $response['data']['description']);
        $this->assertSame($yearLaunched, $response['data']['year_launched']);
        $this->assertSame($duration, $response['data']['duration']);
        $this->assertSame($rating->value, $response['data']['rating']);
        $this->assertSame($opened, $response['data']['opened']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

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
        $this->assertCount($nCategories, $response['data']['categories_id']);
        $this->assertCount($nGenres, $response['data']['genres_id']);
        $this->assertCount($nCastMembers, $response['data']['cast_members_id']);
        $this->assertEquals($categoriesIds, $response['data']['categories_id']);
        $this->assertEquals($genresIds, $response['data']['genres_id']);
        $this->assertEquals($castMembersIds, $response['data']['cast_members_id']);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $response['data']['id'],
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->videos);
        }
        // verificando o relacionamento a partir de genre
        foreach ($genresIds as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $response['data']['id'],
                'genre_id' => $genreId,
            ]);
            $genreModel = GenreModel::find($genreId);
            $this->assertCount(1, $genreModel->videos);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($castMembersIds as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $response['data']['id'],
                'cast_member_id' => $castMemberId,
            ]);
            $castMemberModel = CastMemberModel::find($castMemberId);
            $this->assertCount(1, $castMemberModel->videos);
        }

        // verificando se os arquivos de image foram registrados no bd
        $this->assertDatabaseCount('video_images', 3);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response['data']['id'],
            'path' => $response['data']['thumbfile'],
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response['data']['id'],
            'path' => $response['data']['thumbhalf'],
        ]);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $response['data']['id'],
            'path' => $response['data']['bannerfile'],
        ]);

        // verificando se os arquivos de media foram registrados no bd
        $this->assertDatabaseCount('video_medias', 2);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $response['data']['id'],
            'file_path' => $response['data']['trailerfile'],
        ]);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $response['data']['id'],
            'file_path' => $response['data']['videofile'],
        ]);

        // verificando se os arquivos foram armazenados
        Storage::assertExists($response['data']['thumbfile']);
        Storage::assertExists($response['data']['thumbhalf']);
        Storage::assertExists($response['data']['bannerfile']);
        Storage::assertExists($response['data']['trailerfile']);
        Storage::assertExists($response['data']['videofile']);

        // apagando a pasta com os arquivos criados
        Storage::deleteDirectory($response['data']['id']);
    }

    // testando o método store, com falhas na validação
    public function testStoreValidationFailure()
    {
        // definindo os dados a serem passados no body
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;

        // validando o atributo title
        // definindo os dados a serem passados no body
        $data = [
            'title' => '',
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
            ]
        ]);

        // validando o atributo description
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => '',
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'description',
            ]
        ]);

        // validando o atributo year_launched
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => '',
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'year_launched',
            ]
        ]);

        // validando o atributo duration
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => '',
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'duration',
            ]
        ]);

        // validando o atributo opened
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => 'fake',
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'opened',
            ]
        ]);
        // validando o atributo opened
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => 'fake',
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'opened',
            ]
        ]);

        // validando o atributo rating
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => '',
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'rating',
            ]
        ]);

        // validando o atributo categories_id
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'categories_id' => ['fake']
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'categories_id',
            ]
        ]);

        // validando o atributo genres_id
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'genres_id' => ['fake']
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'genres_id',
            ]
        ]);

        // validando o atributo cast_members_id
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'cast_members_id' => ['fake']
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'cast_members_id',
            ]
        ]);

        // validando o atributo thumbfile
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'thumbfile' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'thumbfile',
            ]
        ]);

        // validando o atributo thumbhalf
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'thumbhalf' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'thumbhalf',
            ]
        ]);

        // validando o atributo bannerfile
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'bannerfile' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'bannerfile',
            ]
        ]);

        // validando o atributo trailerfile
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'trailerfile' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'trailerfile',
            ]
        ]);

        // validando o atributo videofile
        // definindo os dados a serem passados no body
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
            'videofile' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'videofile',
            ]
        ]);

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'duration' => '',
            'opened' => 'fake',
            'rating' => '',
            'categories_id' => ['fake'],
            'genres_id' => ['fake'],
            'cast_members_id' => ['fake'],
            'thumbfile' => 'fake',
            'thumbhalf' => 'fake',
            'bannerfile' => 'fake',
            'trailerfile' => 'fake',
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'description',
                'year_launched',
                'duration',
                'opened',
                'rating',
                'categories_id',
                'genres_id',
                'cast_members_id',
                'thumbfile',
                'thumbhalf',
                'bannerfile',
                'trailerfile',
            ]
        ]);
    }

    // testando o método update com id inexistente
    public function testUpdateNotFound()
    {
        // definindo os dados a serem passados no body
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/fake_id", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    // testando o método update
    public function testUpdate()
    {
        // inserindo um registro no bd
        $video = VideoModel::factory()->create();

        // definindo os dados a serem passados no body
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 180;
        $opened = false;
        $rating = Rating::RATE10;
        $data = [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'opened' => $opened,
            'rating' => $rating,
        ];

        // fazendo o request
        sleep(1);
        $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'year_launched',
                'duration',
                'rating',
                'opened',
                'categories_id',
                'genres_id',
                'cast_members_id',
                'thumbfile',
                'thumbhalf',
                'bannerfile',
                'trailerfile',
                'videofile',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($video->id, $response['data']['id']);
        $this->assertSame($title, $response['data']['title']);
        $this->assertSame($description, $response['data']['description']);
        $this->assertSame($yearLaunched, $response['data']['year_launched']);
        $this->assertSame($duration, $response['data']['duration']);
        $this->assertSame($rating->value, $response['data']['rating']);
        $this->assertSame($opened, $response['data']['opened']);
        $this->assertSame(Carbon::make($video->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertNotSame(Carbon::make($video->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);

        $this->assertDatabaseHas('videos', [
            'title' => $title,
            'description' => $description,
            'year_launched' => $yearLaunched,
            'duration' => $duration,
            'rating' => $rating,
            'opened' => $opened,
        ]);
    }

    // // testando o método update
    // public function testUpdateWithCategories()
    // {
    //     // criando as categorias
    //     $qtd = random_int(10, 20);
    //     $categories = CategoryModel::factory()->count($qtd)->create();
    //     // obtendo o array de id das categorias
    //     $categoriesIds = $categories->pluck('id')->toArray();

    //     // inserindo um registro no bd
    //     $video = VideoModel::factory()->create();

    //     // alterando o valor do isActive
    //     $isActiveAlternate = ($video->is_active) ? false : true;

    //     // definindo os dados a serem passados no body
    //     $name = 'name updated';
    //     $data = [
    //         'name' => $name,
    //         'is_active' => $isActiveAlternate,
    //         'categories_id' => $categoriesIds
    //     ];

    //     // fazendo o request
    //     sleep(1);
    //     $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_OK);
    //     $response->assertJsonStructure([
    //         'data' => [
    //             'id',
    //             'name',
    //             'is_active',
    //             'created_at',
    //             'updated_at',
    //         ]
    //     ]);
    //     $this->assertSame($video->id, $response['data']['id']);
    //     $this->assertSame($name, $response['data']['name']);
    //     $this->assertSame($isActiveAlternate, $response['data']['is_active']);
    //     $this->assertSame(Carbon::make($video->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
    //     $this->assertNotSame(Carbon::make($video->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);

    //     $this->assertDatabaseHas('genres', [
    //         'id' => $video->id,
    //         'name' => $name,
    //         'is_active' => $isActiveAlternate,
    //     ]);

    //     $this->assertDatabaseCount('category_genre', $qtd);
    // }

    // // testando o método update passando valores vazios
    // public function testUpdateEmptyValues()
    // {       
    //     // inserindo um registro no bd
    //     $video = VideoModel::factory()->create();

    //     // definindo os dados a serem passados no body
    //     $data = [
    //         'name' => '',
    //         'is_active' => '',
    //         'categories_id' => ''
    //     ];

    //     // fazendo o request
    //     $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'name',
    //             'is_active',
    //             'categories_id',
    //         ]
    //     ]);
    // }

    // // testando o método update, com falhas na validação
    // public function testUpdateValidationFailure()
    // {
    //     // criando as categorias
    //     $qtd = random_int(10, 20);
    //     $categories = CategoryModel::factory()->count($qtd)->create();
    //     // obtendo o array de id das categorias
    //     $categoriesIds = $categories->pluck('id')->toArray();

    //     // inserindo um registro no bd
    //     $video = VideoModel::factory()->create();

    //     // alterando o valor do isActive
    //     $isActiveAlternate = ($video->is_active) ? false : true;

    //     // validando o atributo name
    //     // definindo os dados a serem passados no body
    //     $data = [
    //         'name' => 'n',
    //         'is_active' => $isActiveAlternate,
    //         'categories_id' => $categoriesIds
    //     ];

    //     // fazendo o request
    //     $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'name',
    //         ]
    //     ]);

    //     // validando o atributo is_active
    //     // definindo os dados a serem passados no body
    //     $data = [
    //         'name' => 'name',
    //         'is_active' => 'fake',
    //         'categories_id' => $categoriesIds
    //     ];

    //     // fazendo o request
    //     $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'is_active',
    //         ]
    //     ]);

    //     // validando o atributo categories_id
    //     // definindo os dados a serem passados no body
    //     $data = [
    //         'name' => 'name',
    //         'is_active' => true,
    //         'categories_id' => ['fake']
    //     ];

    //     // fazendo o request        
    //     $response = $this->putJson("{$this->endpoint}/{$video->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'categories_id',
    //         ]
    //     ]);

    //     // validando todos os atributos
    //     // definindo os dados a serem passados no body
    //     $data = [
    //         'name' => 'n',
    //         'is_active' => 'fake',
    //         'categories_id' => ['fake']
    //     ];

    //     // fazendo o request
    //     $response = $this->postJson($this->endpoint, $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'name',
    //             'is_active',
    //             'categories_id',
    //         ]
    //     ]);
    // }

    // // testando o método destroy com id inexistente
    // public function testDestroyNotFound()
    // {
    //     // fazendo o request
    //     $response = $this->deleteJson("{$this->endpoint}/fake_id");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NOT_FOUND);
    // }

    // // testando o método destroy
    // public function testDestroy()
    // {
    //     // inserindo um registro no bd
    //     $video = VideoModel::factory()->create();

    //     // fazendo o request
    //     $response = $this->deleteJson("{$this->endpoint}/{$video->id}");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NO_CONTENT);

    //     $this->assertSoftDeleted('genres', [
    //         'id' => $video->id
    //     ]);
    // }

    // // testando o método destroy com categorias
    // public function testDestroyWithCategories()
    // {
    //     // criando as categorias
    //     $qtd = random_int(10, 20);
    //     $categories = CategoryModel::factory()->count($qtd)->create();
    //     // obtendo o array de id das categorias
    //     $categoriesIds = $categories->pluck('id')->toArray();

    //     // definindo os dados a serem passados no body
    //     $name = 'name video';
    //     $isActive = false;
    //     $data = [
    //         'name' => $name,
    //         'is_active' => $isActive,
    //         'categories_id' => $categoriesIds
    //     ];

    //     // inserindo o registro no bd
    //     $responseStore = $this->postJson($this->endpoint, $data);

    //     // fazendo o request
    //     $response = $this->deleteJson("{$this->endpoint}/{$responseStore['data']['id']}");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NO_CONTENT);

    //     $this->assertSoftDeleted('genres', [
    //         'id' => $responseStore['data']['id']
    //     ]);

    //     $this->assertDatabaseCount('category_genre', $qtd);
    // }
}
