<?php

namespace Tests\Feature\Api;

use App\Models\Category as CategoryModel;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryApiFeatureTest extends TestCase
{
    // atributos
    protected $endpoint = '/api/categories';

    // testando o método index com retorno vazio
    public function testIndexWithNoCategories()
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
        CategoryModel::factory()->count($total)->create();

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
        $category = CategoryModel::factory()->create();

        // fazendo o request
        $response = $this->getJson("{$this->endpoint}/{$category->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($category->id, $response['data']['id']);
        $this->assertSame($category->name, $response['data']['name']);
        $this->assertSame($category->description, $response['data']['description']);
        $this->assertSame($category->is_active, $response['data']['is_active']);
        $this->assertEquals($category->created_at, $response['data']['created_at']);
        $this->assertEquals($category->updated_at, $response['data']['updated_at']);
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
                'name',
            ]
        ]);
    }

    // testando o método store
    public function testStore()
    {
        // definindo os dados a serem passados no body
        $name = 'name test';
        $description = 'desc test';
        $isActive = false;
        $data = [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($description, $response['data']['description']);
        $this->assertSame($isActive, $response['data']['is_active']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
        ]);
    }

    // testando o método store, com falhas na validação
    public function testStoreValidationFailure()
    {
        // validando o atributo name
        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'description' => 'description',
            'is_active' => true,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ]
        ]);

        // validando o atributo description
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'description' => 'de',
            'is_active' => true,
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

        // validando o atributo is_active
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'description' => 'description',
            'is_active' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'is_active',
            ]
        ]);

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'description' => 'de',
            'is_active' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'description',
                'is_active',
            ]
        ]);
    }

    // testando o método update com id inexistente
    public function testUpdateNotFound()
    {
        // definindo os dados a serem passados no body        
        $name = 'name updated';
        $description = 'desc updated';
        $isActive = false;
        $data = [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive,
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
        $category = CategoryModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($category->is_active) ? false : true;

        // definindo os dados a serem passados no body
        $name = 'name updated';
        $description = 'desc updated';
        $data = [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActiveAlternate,
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$category->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($category->id, $response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($description, $response['data']['description']);
        $this->assertSame($isActiveAlternate, $response['data']['is_active']);
        $this->assertNotSame($category->created_at, $response['data']['created_at']);
        $this->assertNotSame($category->updated_at, $response['data']['updated_at']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $name,
            'description' => $description,
            'is_active' => $isActiveAlternate,
        ]);
    }

    // testando o método destroy com id inexistente
    public function testDestroyNotFound()
    {
        // fazendo o request
        $response = $this->deleteJson("{$this->endpoint}/fake_id");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    // testando o método destroy
    public function testDestroy()
    {
        // inserindo um registro no bd
        $category = CategoryModel::factory()->create();

        // fazendo o request
        $response = $this->deleteJson("{$this->endpoint}/{$category->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('categories', [
            'id' => $category->id
        ]);
    }
}
