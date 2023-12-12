<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\App\Http\Controllers\Api;

// importações

use App\Http\Controllers\Api\CategoryController;
use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        // executando o index
        $response = $controller->index(new Request(), $usecase);

        // verificando os dados
        $this->assertInstanceOf(AnonymousResourceCollection::class, $response);
        $this->assertArrayHasKey('meta', $response->additional);
    }
}
