<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Api\GenreController;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Eloquent\GenreEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\UseCase\Genre\DeleteByIdGenreUseCase;
use Core\UseCase\Genre\FindByIdGenreUseCase;
use Core\UseCase\Genre\InsertGenreUseCase;
use Core\UseCase\Genre\PaginateGenreUseCase;
use Core\UseCase\Genre\UpdateGenreUseCase;
use Core\UseCase\Interfaces\TransactionDbInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class GenreControllerFeatureTest extends TestCase
{

    // atributos
    protected GenreEloquentRepository $repository;
    protected TransactionDbInterface $transactionDb;
    protected CategoryRepositoryInterface $categoryRepository;

    // sobrescrevendo a função de preparação da classe mãe
    // é executada antes dos testes
    protected function setUp(): void
    {
        // reutilizando as instruções da classe mãe
        parent::setUp();
        // instanciando o repository
        $this->repository = new GenreEloquentRepository(new GenreModel());
        // instanciando o gerenciador de transações
        $this->transactionDb = new TransactionDb();
        // instanciando o repository da category
        $this->categoryRepository = new CategoryEloquentRepository(new CategoryModel());
    }

    // testando o método index
    public function testIndex()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        GenreModel::factory()->count($qtd)->create();

        // instanciando o usecase
        $usecase = new PaginateGenreUseCase($this->repository);

        // instanciando o controller
        $controller = new GenreController();
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

    // testando o método store
    public function testStore()
    {
        // instanciando o usecase
        $usecase = new InsertGenreUseCase($this->repository, $this->transactionDb, $this->categoryRepository);

        // instanciando o controller
        $controller = new GenreController();
        // configurando o request com validação específica
        $storeRequest = new StoreGenreRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->setJson(new ParameterBag([
            'name' => 'name test',
            'is_active' => false,
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());

        $this->assertDatabaseHas('genres', [
            'name' => 'name test',
            'is_active' => false
        ]);
    }

    // testando o método store
    public function testStoreWithCategories()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();
        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // instanciando o usecase
        $usecase = new InsertGenreUseCase($this->repository, $this->transactionDb, $this->categoryRepository);

        // instanciando o controller
        $controller = new GenreController();
        // configurando o request com validação específica
        $storeRequest = new StoreGenreRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->setJson(new ParameterBag([
            'name' => 'name test',
            'is_active' => false,
            'categories_id' => $categoriesIds
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());

        $this->assertDatabaseHas('genres', [
            'name' => 'name test',
            'is_active' => false
        ]);

        // verificando relacionamentos
        $this->assertDatabaseCount('category_genre', $qtd);
        $this->assertCount($qtd, $response->getData()->data->categories_id);
        $this->assertEquals($categoriesIds, $response->getData()->data->categories_id);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('category_genre', [
                'genre_id' => $response->getData()->data->id,
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->genres);
        }
    }

    // testando o método show
    public function testShow()
    {
        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // instanciando o usecase
        $usecase = new FindByIdGenreUseCase($this->repository);

        // instanciando o controller
        $controller = new GenreController();

        // executando o show
        $response = $controller->show($genre->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
    }

    // testando o método update
    public function testUpdate()
    {
        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($genre->is_active) ? false : true;

        // instanciando o usecase
        $usecase = new UpdateGenreUseCase($this->repository, $this->transactionDb, $this->categoryRepository);

        // instanciando o controller
        $controller = new GenreController();
        // configurando o request com validação específica
        $updateRequest = new UpdateGenreRequest();
        $updateRequest->headers->set('content-type', 'application/json');
        $updateRequest->setJson(new ParameterBag([
            'name' => 'name updated',
            'is_active' => $isActiveAlternate,
        ]));

        // executando o update
        $response = $controller->update($genre->id, $updateRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'is_active' => $isActiveAlternate,
        ]);
    }

    // testando o método destroy
    public function testDestroy()
    {
        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // instanciando o usecase
        $usecase = new DeleteByIdGenreUseCase($this->repository);

        // instanciando o controller
        $controller = new GenreController();

        // executando o destroy
        $response = $controller->destroy($genre->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->status());

        $this->assertSoftDeleted('genres', [
            'id' => $genre->id
        ]);
    }
}
