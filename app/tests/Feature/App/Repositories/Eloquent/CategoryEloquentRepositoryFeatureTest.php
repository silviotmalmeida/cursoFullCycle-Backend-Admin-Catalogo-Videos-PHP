<?php

namespace Tests\Feature\App\Repositories\Eloquent;

use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Models\Category as CategoryModel;
use Core\Domain\Entity\Category as CategoryEntity;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\PaginationInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryEloquentRepositoryFeatureTest extends TestCase
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
        $this->repository = new CategoryEloquentRepository(new CategoryModel());
    }

    // testando se o repositório implementa a interface definida
    public function testImplementsInterface()
    {
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
    }

    // testando a função de inserção no bd
    public function testInsert()
    {
        // criando a entidade
        $entity = new CategoryEntity(name: 'test');
        // inserindo no bd
        $response = $this->repository->insert($entity);
        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertDatabaseHas('categories', [
            'id' => $entity->id()
        ]);
    }

    // testando a função de inserção no bd
    public function testInsertDeactivate()
    {
        // criando a entidade
        $entity = new CategoryEntity(name: 'test');
        $entity->deactivate();
        // inserindo no bd
        $response = $this->repository->insert($entity);
        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertDatabaseHas('categories', [
            'id' => $entity->id(),
            'is_active' => false,
        ]);
    }

    // testando a função de busca por id no bd, com sucesso na busca
    public function testFindById()
    {
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findById($model->id);
        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertSame($model->id, $response->id());
    }

    // testando a função de busca por id no bd, sem sucesso na busca
    public function testFindByIdNotFound()
    {
        try {
            // buscando no bd
            $this->repository->findById('fake');
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), 'ID not found');
        }
    }

    // testando a função de busca múltipla por id no bd, com sucesso na busca
    public function testFindByIdArray()
    {
        // inserindo múlttiplos registros no bd
        $model1 = CategoryModel::factory()->create();
        $model2 = CategoryModel::factory()->create();
        $model3 = CategoryModel::factory()->create();
        $model4 = CategoryModel::factory()->create();
        $model5 = CategoryModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findByIdArray([
            $model1->id,
            $model3->id,
            $model5->id,
        ]);
        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response[0]);
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
        $model1 = CategoryModel::factory()->create();
        $model2 = CategoryModel::factory()->create();
        $model3 = CategoryModel::factory()->create();
        $model4 = CategoryModel::factory()->create();
        $model5 = CategoryModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findByIdArray([
            $model1->id,
            $model3->id,
            Uuid::uuid4()->toString(),
        ]);
        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response[0]);
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
        $model1 = CategoryModel::factory()->create();
        $model2 = CategoryModel::factory()->create();
        $model3 = CategoryModel::factory()->create();
        $model4 = CategoryModel::factory()->create();
        $model5 = CategoryModel::factory()->create();
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
        CategoryModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertCount($qtd, $response);
    }

    // testando a função de busca geral no bd, semm sucesso na busca
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
        CategoryModel::factory()->count($qtd)->create([
            'name' => 'abcde',
        ]);
        // criando registros sem o filtro a ser aplicado
        CategoryModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->findAll(
            filter: 'abcde'
        );
        // verificando
        $this->assertCount($qtd, $response);
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertEquals($qtd*2, count($response));
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
        CategoryModel::factory()->count($qtd)->create();
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
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();
        // criando uma entidade equivalente ao registro, mas com name atualizado
        $category = new CategoryEntity(
            id: $model->id,
            name: "updated name"
        );
        // inserindo no bd
        sleep(1);
        $response = $this->repository->update($category);

        // verificando
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertSame($model->id, $response->id());
        $this->assertSame("updated name", $response->name);
        $this->assertNotEquals($model->name, $response->name);
        $this->assertNotEquals($model->updated_at, $response->updatedAt);
    }

    // testando a função de update no bd, sem sucesso na busca
    public function testUpdateNotFound()
    {
        try {
            // criando uma entidade que não existe no bd
            $category = new CategoryEntity(name: "fake");
            // buscando no bd
            $this->repository->update($category);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), 'ID not found');
        }
    }

    // testando a função de delete por id no bd, com sucesso na busca
    public function testDeleteById()
    {
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();
        // deletando no bd
        $response = $this->repository->deleteById($model->id);
        // verificando
        $this->assertTrue($response);
        // soft-delete
        $this->assertDatabaseCount('categories', 1);
        $this->assertSoftDeleted(table: 'categories', data: ['id' => $model->id], deletedAtColumn: 'deleted_at');
    }

    // testando a função de delete por id no bd, sem sucesso na busca
    public function testDeleteByIdNotFound()
    {
        try {
            // buscando no bd
            $this->repository->deleteById('fake');
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), 'ID not found');
        }
    }
}
