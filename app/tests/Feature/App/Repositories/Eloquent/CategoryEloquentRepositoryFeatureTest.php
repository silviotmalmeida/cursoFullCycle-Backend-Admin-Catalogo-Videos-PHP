<?php

namespace Tests\Feature\App\Repositories\Eloquent;

use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Models\Category as CategoryModel;
use Core\Domain\Entity\Category as CategoryEntity;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Exception\NotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $entity = CategoryModel::factory()->create();
        // buscando no bd
        $response = $this->repository->findById($entity->id);
        // verificando
        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(CategoryEntity::class, $response);
        $this->assertSame($entity->id, $response->id());
    }

    // testando a função de busca por id no bd, sem sucesso na busca
    public function testFindByIdNotFound()
    {
        try {
            // buscando no bd
            $response = $this->repository->findById('fake');
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
}
