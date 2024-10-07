<?php

namespace Tests\Feature\App\Repositories\Eloquent;

use App\Repositories\Eloquent\VideoEloquentRepository;
use App\Models\Video as VideoModel;
use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use App\Models\CastMember as CastMemberModel;
use Core\Domain\Entity\Video as VideoEntity;
use Core\Domain\Enum\ImageType;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Enum\Rating;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\PaginationInterface;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class VideoEloquentRepositoryFeatureTest extends TestCase
{
    // declarando o repository
    protected $repository;

    // sobrescrevendo a função de preparação da classe mãe
    // é executada antes dos testes
    protected function setUp(): void
    {
        // reutilizando as instruções da classe mãe
        parent::setUp();
        // instanciando o repository
        $this->repository = new VideoEloquentRepository(new VideoModel());
    }

    // testando se o repositório implementa a interface definida
    public function testImplementsInterface()
    {
        $this->assertInstanceOf(VideoRepositoryInterface::class, $this->repository);
    }

    // testando a função de inserção no bd
    public function testInsert()
    {
        // valores a serem considerados
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $rating = Rating::RATE10;

        // criando a entidade
        $entity = new VideoEntity(
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            rating: $rating
        );
        // inserindo no bd
        $response = $this->repository->insert($entity);
        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertDatabaseHas('videos', [
            'id' => $entity->id(),
            'title' => $entity->title,
            'description' => $entity->description,
            'year_launched' => $entity->yearLaunched,
            'duration' => $entity->duration,
            'rating' => $entity->rating,
            'opened' => $entity->opened,
            'created_at' => $entity->createdAt(),
            'updated_at' => $entity->updatedAt(),
        ]);
    }

    // testando a função de inserção no bd
    public function testInsertOpened()
    {
        // valores a serem considerados
        $title = 'title';
        $description = 'description';
        $yearLaunched = 2024;
        $duration = 120;
        $rating = Rating::RATE10;

        // criando a entidade
        $entity = new VideoEntity(
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            rating: $rating
        );
        // abrindo a entidade
        $entity->open();
        // inserindo no bd
        $response = $this->repository->insert($entity);
        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertDatabaseHas('videos', [
            'id' => $entity->id(),
            'title' => $entity->title,
            'description' => $entity->description,
            'year_launched' => $entity->yearLaunched,
            'duration' => $entity->duration,
            'rating' => $entity->rating,
            'opened' => $entity->opened,
            'created_at' => $entity->createdAt(),
            'updated_at' => $entity->updatedAt(),
        ]);
    }

    // testando a função de inserção no bd
    public function testInsertWithRelationships()
    {
        // gerando massa de dados a serem utilizados nos relacionamentos
        // 
        // definindo número randomico de categorias
        $nCategories = rand(1, 9);
        // criando categorias no bd para possibilitar os relacionamentos
        $categories = CategoryModel::factory()->count($nCategories)->create();
        $this->assertDatabaseCount('categories', $nCategories);
        // 
        // definindo número randomico de genres
        $nGenres = rand(1, 9);
        // criando genres no bd para possibilitar os relacionamentos
        $genres = GenreModel::factory()->count($nGenres)->create();
        $this->assertDatabaseCount('genres', $nGenres);
        // 
        // definindo número randomico de castMembers
        $nCastMembers = rand(1, 9);
        // criando castMembers no bd para possibilitar os relacionamentos
        $castMembers = CastMemberModel::factory()->count($nCastMembers)->create();
        $this->assertDatabaseCount('cast_members', $nCastMembers);

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // adicionando as categorias
        foreach ($categories as $category) {
            $entity->addCategoryId($category->id);
        }
        // adicionando os genres
        foreach ($genres as $genre) {
            $entity->addGenreId($genre->id);
        }
        // adicionando os castMembers
        foreach ($castMembers as $castMember) {
            $entity->addCastMemberId($castMember->id);
        }
        // inserindo no bd
        $response = $this->repository->insert($entity);

        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertDatabaseHas('videos', [
            'id' => $entity->id()
        ]);
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);
        $this->assertCount($nCategories, $response->categoriesId);
        $this->assertCount($nGenres, $response->genresId);
        $this->assertCount($nCastMembers, $response->castMembersId);
        $this->assertEquals($categories->pluck('id')->toArray(), $response->categoriesId);
        $this->assertEquals($genres->pluck('id')->toArray(), $response->genresId);
        $this->assertEquals($castMembers->pluck('id')->toArray(), $response->castMembersId);

        $videoModel = VideoModel::find($entity->id());
        $this->assertCount($nCategories, $videoModel->categories);
        $this->assertCount($nGenres, $videoModel->genres);
        $this->assertCount($nCastMembers, $videoModel->castMembers);

        // verificando o relacionamento a partir de category
        foreach ($categories as $category) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $entity->id(),
                'category_id' => $category->id,
            ]);
            $categoryModel = CategoryModel::find($category->id);
            $this->assertCount(1, $categoryModel->videos);
        }
        // verificando o relacionamento a partir de genre
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $entity->id(),
                'genre_id' => $genre->id,
            ]);
            $genreModel = GenreModel::find($genre->id);
            $this->assertCount(1, $genreModel->videos);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($castMembers as $castMember) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $entity->id(),
                'cast_member_id' => $castMember->id,
            ]);
            $castMemberModel = CastMemberModel::find($castMember->id);
            $this->assertCount(1, $castMemberModel->videos);
        }
    }

    // testando a função de inserção no bd com a inclusão de trailer
    public function testInsertWithMediaTrailer()
    {
        // valores a serem considerados
        $filePath = 'path_do_trailer.mp4';
        $mediaStatus = MediaStatus::PENDING;
        $mediaType = MediaType::TRAILER;
        $encodedPath = '';

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a media
        $media = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media
        $entity->setTrailerFile($media);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_medias', 0);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $entityDb = $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $entity->id(),
            'file_path' => $media->filePath(),
            'encoded_path' => $media->encodedPath(),
            'status' => $media->mediaStatus(),
            'type' => $media->mediaType(),
        ]);
        $this->assertNotNull($entityDb->trailerFile());
    }

    // testando a função de inserção no bd com a inclusão de video
    public function testInsertWithMediaVideo()
    {
        // valores a serem considerados
        $filePath = 'path_do_video.mp4';
        $mediaStatus = MediaStatus::PENDING;
        $mediaType = MediaType::VIDEO;
        $encodedPath = '';

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a media
        $media = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media
        $entity->setVideoFile($media);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_medias', 0);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $entityDb = $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $entity->id(),
            'file_path' => $media->filePath(),
            'encoded_path' => $media->encodedPath(),
            'status' => $media->mediaStatus(),
            'type' => $media->mediaType(),
        ]);
        $this->assertNotNull($entityDb->videoFile());
    }

    // testando a função de inserção no bd com a inclusão de thumb
    public function testInsertWithImageThumb()
    {
        // valores a serem considerados
        $filePath = 'path_do_thumb.mp4';
        $imageType = ImageType::THUMB;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setThumbFile($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_images', 0);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $entityDb = $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image->filePath(),
            'type' => $image->imageType(),
        ]);
        $this->assertNotNull($entityDb->thumbFile());
    }

    // testando a função de inserção no bd com a inclusão de thumbHalf
    public function testInsertWithImageThumbHalf()
    {
        // valores a serem considerados
        $filePath = 'path_do_thumbHalf.mp4';
        $imageType = ImageType::THUMB_HALF;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setThumbHalf($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_images', 0);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $entityDb = $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image->filePath(),
            'type' => $image->imageType(),
        ]);
        $this->assertNotNull($entityDb->thumbHalf());
    }

    // testando a função de inserção no bd com a inclusão de banner
    public function testInsertWithImageBanner()
    {
        // valores a serem considerados
        $filePath = 'path_do_banner.mp4';
        $imageType = ImageType::BANNER;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setBannerFile($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_images', 0);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $entityDb = $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image->filePath(),
            'type' => $image->imageType(),
        ]);
        $this->assertNotNull($entityDb->bannerFile());
    }

    // testando a função de busca por id no bd, com sucesso na busca
    public function testFindById()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findById($model->id);
        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertSame($model->id, $response->id());
    }

    // testando a função de busca por id no bd, sem sucesso na busca
    public function testFindByIdNotFound()
    {
        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('ID not found');
        // buscando no bd
        $this->repository->findById('fake');
    }

    // testando a função de busca múltipla por id no bd, com sucesso na busca
    public function testFindByIdArray()
    {
        // inserindo múlttiplos registros no bd
        $model1 = VideoModel::factory()->create();
        $model2 = VideoModel::factory()->create();
        $model3 = VideoModel::factory()->create();
        $model4 = VideoModel::factory()->create();
        $model5 = VideoModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findByIdArray([
            $model1->id,
            $model3->id,
            $model5->id,
        ]);
        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response[0]);
        $this->assertCount(3, $response);
        $this->assertContains(
            $response[0]->id(),
            [$model1->id, $model3->id, $model5->id]
        );
        $this->assertContains(
            $response[1]->id(),
            [$model1->id, $model3->id, $model5->id]
        );
        $this->assertContains(
            $response[2]->id(),
            [$model1->id, $model3->id, $model5->id]
        );
    }

    // testando a função de busca múltipla por id no bd, com sucesso na busca para alguns valores
    public function testFindByIdArrayFoundSome()
    {
        // inserindo múlttiplos registros no bd
        $model1 = VideoModel::factory()->create();
        $model2 = VideoModel::factory()->create();
        $model3 = VideoModel::factory()->create();
        $model4 = VideoModel::factory()->create();
        $model5 = VideoModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findByIdArray([
            $model1->id,
            $model3->id,
            Uuid::uuid4()->toString(),
        ]);
        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response[0]);
        $this->assertCount(2, $response);
        $this->assertContains(
            $response[0]->id(),
            [$model1->id, $model3->id]
        );
        $this->assertContains(
            $response[1]->id(),
            [$model1->id, $model3->id]
        );
    }

    // testando a função de busca múltipla por id no bd, sem sucesso na busca
    public function testFindByIdArrayFoundNone()
    {
        // inserindo múlttiplos registros no bd
        $model1 = VideoModel::factory()->create();
        $model2 = VideoModel::factory()->create();
        $model3 = VideoModel::factory()->create();
        $model4 = VideoModel::factory()->create();
        $model5 = VideoModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findByIdArray([
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
        ]);
        // verificando
        $this->assertCount(0, $response);
    }

    // testando a função de busca geral no bd, com sucesso na busca
    public function testFindAll()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = rand(30, 60);
        // inserindo múltiplos registros no bd
        VideoModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertCount($qtd, $response);
    }

    // testando a função de busca geral no bd, sem sucesso na busca
    public function testFindAllEmpty()
    {
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertCount(0, $response);
    }

    // testando a função de busca geral no bd, com filtro
    public function testFindAllWithFilter()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = rand(30, 60);
        // criando registros com o filtro a ser aplicado
        VideoModel::factory()->count($qtd)->create([
            'title' => 'abcde',
        ]);
        // criando registros sem o filtro a ser aplicado
        VideoModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->findAll(
            filter: 'abcde'
        );
        // verificando
        $this->assertCount($qtd, $response);
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertEquals($qtd * 2, count($response));
    }

    // provedor de dados do testPaginate
    public function dataProviderTestPaginate(): array
    {
        return [
            [
                'qtd' => 25,
                'page' => 1,
                'perPage' => 10,
                'items' => 10
            ],
            [
                'qtd' => 25,
                'page' => 2,
                'perPage' => 10,
                'items' => 10
            ],
            [
                'qtd' => 25,
                'page' => 3,
                'perPage' => 10,
                'items' => 5
            ],
        ];
    }
    // testando a função de busca geral paginada no bd, com sucesso na busca
    // utiliza o dataProvider dataProviderTestPaginate
    /**
     * @dataProvider dataProviderTestPaginate
     */
    public function testPaginate(
        int $qtd,
        int $page,
        int $perPage,
        int $items
    ) {
        // inserindo múltiplos registros no bd
        VideoModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->paginate(
            page: $page,
            perPage: $perPage
        );
        // verificando
        $this->assertInstanceOf(PaginationInterface::class, $response);
        $this->assertSame($qtd, $response->total());
        $this->assertSame($page, $response->currentPage());
        $this->assertSame($perPage, $response->perPage());
        $this->assertCount($items, $response->items());
    }

    // testando a função de busca geral paginada no bd, sem sucesso na busca
    public function testPaginateEmpty()
    {
        // buscando no bd
        $response = $this->repository->paginate();
        // verificando
        $this->assertInstanceOf(PaginationInterface::class, $response);
        $this->assertCount(0, $response->items());
        $this->assertSame(0, $response->total());
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdate()
    {
        // criando os dados a serem considerados
        $title = 'updated title';
        $description = 'updated description';
        $yearLaunched = rand(1111, 9999);
        $duration = rand(60, 180);
        $typeValues = array_column(Rating::cases(), 'value');
        $rating = $typeValues[array_rand($typeValues)];

        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        // criando uma entidade equivalente ao registro, mas com dados atualizados
        $video = new VideoEntity(
            id: $model->id,
            title: $title,
            description: $description,
            yearLaunched: $yearLaunched,
            duration: $duration,
            rating: $rating
        );
        // atualizando no bd
        sleep(1);
        $response = $this->repository->update($video);

        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertSame($model->id, $response->id());
        $this->assertSame($title, $response->title);
        $this->assertSame($description, $response->description);
        $this->assertSame($yearLaunched, $response->yearLaunched);
        $this->assertSame($duration, $response->duration);
        $this->assertSame($rating, $response->rating->value);
        $this->assertNotEquals($response->createdAt, $response->updatedAt);
        $this->assertNotEquals($model->updated_at, $response->updatedAt);
        $this->assertDatabaseHas('videos', [
            'id' => $video->id(),
            'title' => $video->title,
            'description' => $video->description,
            'year_launched' => $video->yearLaunched,
            'duration' => $video->duration,
            'rating' => $video->rating,
            'opened' => $video->opened,
            'created_at' => $video->createdAt(),
            'updated_at' => $response->updatedAt(),
        ]);
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithRelationships()
    {
        // criando os dados a serem considerados
        $yearLaunched = rand(1111, 9999);
        $duration = rand(60, 180);
        $typeValues = array_column(Rating::cases(), 'value');
        $rating = $typeValues[array_rand($typeValues)];
        $category1 = CategoryModel::factory()->create();
        $category2 = CategoryModel::factory()->create();
        $category3 = CategoryModel::factory()->create();
        $genre1 = GenreModel::factory()->create();
        $genre2 = GenreModel::factory()->create();
        $genre3 = GenreModel::factory()->create();
        $castMember1 = CastMemberModel::factory()->create();
        $castMember2 = CastMemberModel::factory()->create();
        $castMember3 = CastMemberModel::factory()->create();

        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        // criando uma entidade equivalente ao registro, mas com dados atualizados
        $video = new VideoEntity(
            id: $model->id,
            title: 'updated title',
            description: 'updated description',
            yearLaunched: $yearLaunched,
            duration: $duration,
            rating: $rating
        );
        // adicionando as categorias
        $video->addCategoryId($category1->id);
        $video->addCategoryId($category2->id);

        // adicionando os genres
        $video->addGenreId($genre1->id);
        $video->addGenreId($genre2->id);

        // adicionando os castMembers
        $video->addCastMemberId($castMember1->id);
        $video->addCastMemberId($castMember2->id);

        // atualizando no bd
        sleep(1);
        $response = $this->repository->update($video);

        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response);
        $this->assertSame($model->id, $response->id());
        $this->assertSame("updated title", $response->title);
        $this->assertSame("updated description", $response->description);
        $this->assertSame($yearLaunched, $response->yearLaunched);
        $this->assertSame($duration, $response->duration);
        $this->assertSame($rating, $response->rating->value);
        $this->assertNotEquals($response->createdAt, $response->updatedAt);
        $this->assertNotEquals($model->updated_at, $response->updatedAt);
        $this->assertDatabaseCount('video_category', 2);
        $this->assertDatabaseCount('video_genre', 2);
        $this->assertDatabaseCount('video_cast_member', 2);
        $this->assertEquals([$category1->id, $category2->id], $response->categoriesId);
        $this->assertEquals([$genre1->id, $genre2->id], $response->genresId);
        $this->assertEquals([$castMember1->id, $castMember2->id], $response->castMembersId);
        // verificando o relacionamento a partir de category
        foreach ($video->categoriesId as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $video->id(),
                'category_id' => $categoryId,
            ]);
        }
        // verificando o relacionamento a partir de genre
        foreach ($video->genresId as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $video->id(),
                'genre_id' => $genreId,
            ]);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($video->castMembersId as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $video->id(),
                'cast_member_id' => $castMemberId,
            ]);
        }

        // atualizando novamente a entidade
        $video = new VideoEntity(
            id: $model->id,
            title: 'updated title 2',
            description: 'updated description 2',
            yearLaunched: 2024,
            duration: 50,
            rating: Rating::L
        );
        // adicionando as categorias
        $video->addCategoryId($category3->id);

        // adicionando os genres
        $video->addGenreId($genre3->id);

        // adicionando os castMembers
        $video->addCastMemberId($castMember3->id);

        // atualizando no bd
        sleep(1);
        $response2 = $this->repository->update($video);

        // verificando
        $this->assertInstanceOf(VideoEntity::class, $response2);
        $this->assertSame($model->id, $response2->id());
        $this->assertSame("updated title 2", $response2->title);
        $this->assertSame("updated description 2", $response2->description);
        $this->assertSame(2024, $response2->yearLaunched);
        $this->assertSame(50, $response2->duration);
        $this->assertSame('L', $response2->rating->value);
        $this->assertNotEquals($response->createdAt, $response->updatedAt);
        $this->assertNotEquals($model->updated_at, $response2->updatedAt);
        $this->assertDatabaseCount('video_category', 1);
        $this->assertDatabaseCount('video_genre', 1);
        $this->assertDatabaseCount('video_cast_member', 1);
        $this->assertEquals([$category3->id], $response2->categoriesId);
        $this->assertEquals([$genre3->id], $response2->genresId);
        $this->assertEquals([$castMember3->id], $response2->castMembersId);
        // verificando o relacionamento a partir de category
        foreach ($video->categoriesId as $categoryId) {
            $this->assertDatabaseHas('video_category', [
                'video_id' => $video->id(),
                'category_id' => $categoryId,
            ]);
        }
        // verificando o relacionamento a partir de genre
        foreach ($video->genresId as $genreId) {
            $this->assertDatabaseHas('video_genre', [
                'video_id' => $video->id(),
                'genre_id' => $genreId,
            ]);
        }
        // verificando o relacionamento a partir de castMember
        foreach ($video->castMembersId as $castMemberId) {
            $this->assertDatabaseHas('video_cast_member', [
                'video_id' => $video->id(),
                'cast_member_id' => $castMemberId,
            ]);
        }
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithMediaTrailer()
    {
        // valores a serem considerados inicialmente
        $filePath = 'path_do_trailer.mp4';
        $mediaStatus = MediaStatus::PENDING;
        $mediaType = MediaType::TRAILER;
        $encodedPath = '';

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a media
        $media = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media
        $entity->setTrailerFile($media);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        // inserindo a media
        $this->repository->updateMedia($entity);

        // valores a serem considerados na atualização
        $filePath = 'path_do_trailer2.mp4';
        $mediaStatus = MediaStatus::COMPLETE;
        $mediaType = MediaType::TRAILER;
        $encodedPath = 'encoded_path_do_trailer2.mp4';

        // criando a media atualizada
        $media2 = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media atualizada
        $entity->setTrailerFile($media2);
        // atualizando no bd
        sleep(1);
        $this->repository->update($entity);
        // atualizando a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $entity->id(),
            'file_path' => $media2->filePath(),
            'encoded_path' => $media2->encodedPath(),
            'status' => $media2->mediaStatus(),
            'type' => $media2->mediaType(),
        ]);
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithMediaVideo()
    {
        // valores a serem considerados inicialmente
        $filePath = 'path_do_video.mp4';
        $mediaStatus = MediaStatus::PENDING;
        $mediaType = MediaType::VIDEO;
        $encodedPath = '';

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a media
        $media = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media
        $entity->setVideoFile($media);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        // inserindo a media
        $this->repository->updateMedia($entity);

        // valores a serem considerados na atualização
        $filePath = 'path_do_video2.mp4';
        $mediaStatus = MediaStatus::COMPLETE;
        $mediaType = MediaType::VIDEO;
        $encodedPath = 'encoded_path_do_video2.mp4';

        // criando a media atualizada
        $media2 = new Media(
            filePath: $filePath,
            mediaStatus: $mediaStatus,
            mediaType: $mediaType,
            encodedPath: $encodedPath,
        );
        // adicionando a media atualizada
        $entity->setVideoFile($media2);
        // atualizando no bd
        sleep(1);
        $this->repository->update($entity);
        // atualizando a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 1);
        $this->assertDatabaseHas('video_medias', [
            'video_id' => $entity->id(),
            'file_path' => $media2->filePath(),
            'encoded_path' => $media2->encodedPath(),
            'status' => $media2->mediaStatus(),
            'type' => $media2->mediaType(),
        ]);
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithImageThumb()
    {
        // valores a serem considerados inicialmente
        $filePath = 'path_do_thumb.mp4';
        $imageType = ImageType::THUMB;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setThumbFile($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        // inserindo a media
        $this->repository->updateMedia($entity);

        // valores a serem considerados na atualização
        $filePath = 'path_do_thumb2.mp4';
        $imageType = ImageType::THUMB;

        // criando a image atualizada
        $image2 = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image atualizada
        $entity->setThumbFile($image2);
        // atualizando no bd
        sleep(1);
        $this->repository->update($entity);
        // atualizando a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image2->filePath(),
            'type' => $image2->imageType(),
        ]);
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithImageThumbHalf()
    {
        // valores a serem considerados inicialmente
        $filePath = 'path_do_thumbHalf.mp4';
        $imageType = ImageType::THUMB_HALF;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setThumbHalf($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        // inserindo a media
        $this->repository->updateMedia($entity);

        // valores a serem considerados na atualização
        $filePath = 'path_do_thumbHalf2.mp4';
        $imageType = ImageType::THUMB_HALF;

        // criando a image atualizada
        $image2 = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image atualizada
        $entity->setThumbHalf($image2);
        // atualizando no bd
        sleep(1);
        $this->repository->update($entity);
        // atualizando a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image2->filePath(),
            'type' => $image2->imageType(),
        ]);
    }

    // testando a função de update no bd, com sucesso na busca
    public function testUpdateWithImageBanner()
    {
        // valores a serem considerados inicialmente
        $filePath = 'path_do_banner.mp4';
        $imageType = ImageType::BANNER;

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando a image
        $image = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image
        $entity->setBannerFile($image);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        // inserindo a media
        $this->repository->updateMedia($entity);

        // valores a serem considerados na atualização
        $filePath = 'path_do_banner2.mp4';
        $imageType = ImageType::BANNER;

        // criando a image atualizada
        $image2 = new Image(
            filePath: $filePath,
            imageType: $imageType,
        );
        // adicionando a image atualizada
        $entity->setBannerFile($image2);
        // atualizando no bd
        sleep(1);
        $this->repository->update($entity);
        // atualizando a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        // inserindo a media novamente para testar a cardinalidade do relacionamento
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_images', 1);
        $this->assertDatabaseHas('video_images', [
            'video_id' => $entity->id(),
            'path' => $image2->filePath(),
            'type' => $image2->imageType(),
        ]);
    }

    // testando a função de update no bd, sem sucesso na busca
    public function testUpdateNotFound()
    {
        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('ID not found');

        // criando uma entidade que não existe no bd
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // buscando no bd
        $this->repository->update($entity);
    }

    // testando a função de delete por id no bd, com sucesso na busca
    public function testDeleteById()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();
        // deletando no bd
        $response = $this->repository->deleteById($model->id);
        // verificando
        $this->assertTrue($response);
        // soft-delete
        $this->assertDatabaseCount('videos', 1);
        $this->assertSoftDeleted(table: 'videos', data: ['id' => $model->id], deletedAtColumn: 'deleted_at');
    }

    // testando a função de delete por id no bd, com sucesso na busca
    public function testDeleteByIdWithRelationships()
    {
        // gerando massa de dados a serem utilizados nos relacionamentos
        // 
        // definindo número randomico de categorias
        $nCategories = rand(1, 9);
        // criando categorias no bd para possibilitar os relacionamentos
        $categories = CategoryModel::factory()->count($nCategories)->create();
        $this->assertDatabaseCount('categories', $nCategories);
        // 
        // definindo número randomico de genres
        $nGenres = rand(1, 9);
        // criando genres no bd para possibilitar os relacionamentos
        $genres = GenreModel::factory()->count($nGenres)->create();
        $this->assertDatabaseCount('genres', $nGenres);
        // 
        // definindo número randomico de castMembers
        $nCastMembers = rand(1, 9);
        // criando castMembers no bd para possibilitar os relacionamentos
        $castMembers = CastMemberModel::factory()->count($nCastMembers)->create();
        $this->assertDatabaseCount('cast_members', $nCastMembers);

        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // adicionando as categorias
        foreach ($categories as $category) {
            $entity->addCategoryId($category->id);
        }
        // adicionando os genres
        foreach ($genres as $genre) {
            $entity->addGenreId($genre->id);
        }
        // adicionando os castMembers
        foreach ($castMembers as $castMember) {
            $entity->addCastMemberId($castMember->id);
        }
        // inserindo no bd
        $response = $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);

        // deletando no bd
        $response = $this->repository->deleteById($entity->id());
        // verificando
        $this->assertTrue($response);
        // soft-delete
        $this->assertSoftDeleted(table: 'videos', data: ['id' => $entity->id], deletedAtColumn: 'deleted_at');
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_category', $nCategories);
        $this->assertDatabaseCount('video_genre', $nGenres);
        $this->assertDatabaseCount('video_cast_member', $nCastMembers);
    }

    // testando a função de delete no bd com a inclusão de medias
    public function testDeleteWithMedias()
    {
        // criando a entidade
        $entity = new VideoEntity(
            title: 'title',
            description: 'description',
            yearLaunched: 2024,
            duration: 120,
            rating: Rating::RATE10
        );
        // criando o trailer
        $trailer = new Media(
            filePath: 'path_do_trailer.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::TRAILER,
            encodedPath: '',
        );
        $entity->setTrailerFile($trailer);
        // criando o video
        $videoFile = new Media(
            filePath: 'path_do_video.mp4',
            mediaStatus: MediaStatus::PENDING,
            mediaType: MediaType::VIDEO,
            encodedPath: '',
        );
        $entity->setVideoFile($videoFile);
        // criando a thumb
        $thumb = new Image(
            filePath: 'path_do_thumb.mp4',
            imageType: ImageType::THUMB,
        );
        $entity->setThumbFile($thumb);
        // criando a thumbHalf
        $thumbHalf = new Image(
            filePath: 'path_do_thumbHalf.mp4',
            imageType: ImageType::THUMB_HALF,
        );
        $entity->setThumbHalf($thumbHalf);
        // criando o banner
        $banner = new Image(
            filePath: 'path_do_banner.mp4',
            imageType: ImageType::BANNER,
        );
        $entity->setBannerFile($banner);
        // inserindo a entidade no bd
        $this->repository->insert($entity);
        $this->assertDatabaseCount('videos', 1);
        // inserindo a media
        $this->repository->updateMedia($entity);
        $this->assertDatabaseCount('video_medias', 2);
        $this->assertDatabaseCount('video_images', 3);
        // deletando no bd
        $response = $this->repository->deleteById($entity->id());
        // verificando
        $this->assertTrue($response);
        // soft-delete
        $this->assertSoftDeleted(table: 'videos', data: ['id' => $entity->id], deletedAtColumn: 'deleted_at');
        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseCount('video_medias', 2);
        $this->assertDatabaseCount('video_images', 3);
    }

    // testando a função de delete por id no bd, sem sucesso na busca
    public function testDeleteByIdNotFound()
    {
        // definindo as características da exceção a ser lançada
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('ID not found');

        // buscando no bd
        $this->repository->deleteById('fake');
    }
}
