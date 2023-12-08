<?php

namespace Tests\Feature\App\Repositories\Eloquent;

use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Models\Category as CategoryModel;
use Core\Domain\Entity\Category as CategoryEntity;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\PaginationInterface;
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

    // testando a função de inserção no bd
    public function testInsert()
    {
        // criando a entidade
        $entity = new CategoryEntity(name: 'test');
        // inserindo no bd
        $response = $this->repository->insert($entity);
        // verificando
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertDatabaseHas('categories', [
            'name' => $entity->name
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
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
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

    // testando a função de busca geral no bd, com sucesso na busca
    public function testFindAll()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        CategoryModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->findAll();
        // verificando
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
        $this->assertCount($qtd, $response);
    }

    // testando a função de busca geral paginada no bd, com sucesso na busca
    public function testPaginate()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        CategoryModel::factory()->count($qtd)->create();
        // buscando no bd
        $response = $this->repository->paginate();
        // verificando
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(PaginationInterface::class, $response);
        $this->assertCount(15, $response->items());
        $this->assertSame($qtd, $response->total());
    }

    // testando a função de busca geral paginada no bd, sem sucesso na busca
    public function testPaginateEmpty()
    {
        // buscando no bd
        $response = $this->repository->paginate();
        // verificando
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
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
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
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
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
        $this->assertTrue($response);
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
