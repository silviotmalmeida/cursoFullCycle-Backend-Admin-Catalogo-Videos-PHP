<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Api\CategoryController;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\DeleteByIdCategoryUseCase;
use Core\UseCase\Category\FindByIdCategoryUseCase;
use Core\UseCase\Category\InsertCategoryUseCase;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\Category\UpdateCategoryUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CategoryControllerFeatureTest extends TestCase
{

    // atributos
    protected CategoryEloquentRepository $repository;

    // sobrescrevendo a função de preparação da classe mãe
    // é executada antes dos testes
    protected function setUp(): void
    {
        // reutilizando as instruções da classe mãe
        parent::setUp();
        // instanciando o repository
        $this->repository = new CategoryEloquentRepository(new CategoryModel());
    }

    // testando o método index
    public function testIndex()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        CategoryModel::factory()->count($qtd)->create();

        // instanciando o usecase
        $usecase = new PaginateCategoryUseCase($this->repository);

        // instanciando o controller
        $controller = new CategoryController();
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
        $usecase = new InsertCategoryUseCase($this->repository);

        // instanciando o controller
        $controller = new CategoryController();
        // configurando o request com validação específica
        $storeRequest = new StoreCategoryRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->setJson(new ParameterBag([
            'name' => 'name test',
            'description' => 'description test',
            'is_active' => false,
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());

        $this->assertDatabaseHas('categories', [
            'name' => 'name test',
            'description' => 'description test',
            'is_active' => false
        ]);
    }

    // testando o método show
    public function testShow()
    {
        // inserindo um registro no bd
        $category = CategoryModel::factory()->create();

        // instanciando o usecase
        $usecase = new FindByIdCategoryUseCase($this->repository);

        // instanciando o controller
        $controller = new CategoryController();

        // executando o show
        $response = $controller->show($category->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
    }

    // testando o método update
    public function testUpdate()
    {
        // inserindo um registro no bd
        $category = CategoryModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($category->is_active) ? false : true;

        // instanciando o usecase
        $usecase = new UpdateCategoryUseCase($this->repository);

        // instanciando o controller
        $controller = new CategoryController();
        // configurando o request com validação específica
        $updateRequest = new UpdateCategoryRequest();
        $updateRequest->headers->set('content-type', 'application/json');
        $updateRequest->setJson(new ParameterBag([
            'name' => 'name updated',
            'description' => 'description updated',
            'is_active' => $isActiveAlternate,
        ]));

        // executando o update
        $response = $controller->update($category->id, $updateRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $updateRequest->json('name'),
            'description' => $updateRequest->json('description'),
        ]);
    }

    // testando o método destroy
    public function testDestroy()
    {
        // inserindo um registro no bd
        $category = CategoryModel::factory()->create();

        // instanciando o usecase
        $usecase = new DeleteByIdCategoryUseCase($this->repository);

        // instanciando o controller
        $controller = new CategoryController();

        // executando o destroy
        $response = $controller->destroy($category->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->status());

        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
    }
}
