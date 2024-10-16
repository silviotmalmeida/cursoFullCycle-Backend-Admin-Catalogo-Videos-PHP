<?php

namespace Tests\Feature\Api;

use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class GenreApiFeatureTest extends TestCase
{
    // atributos
    private $endpoint = '/api/genres';

    // testando o método index com retorno vazio
    public function testIndexWithNoGenres()
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
        $from = $total > ($currentPage * $perPage) ? ($currentPage * $perPage) : $total;

        // inserindo múltiplos registros no bd
        GenreModel::factory()->count($total)->create();

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
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'is_active',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
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
        $genre = GenreModel::factory()->create();

        // fazendo o request
        $response = $this->getJson("{$this->endpoint}/{$genre->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($genre->id, $response['data']['id']);
        $this->assertSame($genre->name, $response['data']['name']);
        $this->assertSame($genre->is_active, $response['data']['is_active']);
        $this->assertSame(Carbon::make($genre->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertSame(Carbon::make($genre->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);
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
        $name = 'name genre';
        $isActive = false;
        $data = [
            'name' => $name,
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
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($isActive, $response['data']['is_active']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

        $this->assertDatabaseHas('genres', [
            'name' => $name,
            'is_active' => $isActive,
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

        // definindo os dados a serem passados no body
        $name = 'name genre';
        $isActive = false;
        $data = [
            'name' => $name,
            'is_active' => $isActive,
            'categories_id' => $categoriesIds
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($isActive, $response['data']['is_active']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

        $this->assertDatabaseHas('genres', [
            'name' => $name,
            'is_active' => $isActive,
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);
    }

    // testando o método store, com falhas na validação
    public function testStoreValidationFailure()
    {
        // validando o atributo name
        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'is_active' => true,
            'categories_id' => []
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

        // validando o atributo is_active
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'is_active' => 'fake',
            'categories_id' => []
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

        // validando o atributo categories_id
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'is_active' => true,
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

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'is_active' => 'fake',
            'categories_id' => ['fake']
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'is_active',
                'categories_id',
            ]
        ]);
    }

    // testando o método update com id inexistente
    public function testUpdateNotFound()
    {
        // definindo os dados a serem passados no body        
        $name = 'name updated';
        $isActive = false;
        $data = [
            'name' => $name,
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
        $genre = GenreModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($genre->is_active) ? false : true;

        // definindo os dados a serem passados no body
        $name = 'name updated';
        $data = [
            'name' => $name,
            'is_active' => $isActiveAlternate,
        ];

        // fazendo o request
        sleep(1);
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($genre->id, $response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($isActiveAlternate, $response['data']['is_active']);
        $this->assertSame(Carbon::make($genre->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertNotSame(Carbon::make($genre->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $name,
            'is_active' => $isActiveAlternate,
        ]);

        $this->assertDatabaseCount('category_genre', 0);
    }

    // testando o método update
    public function testUpdateWithCategories()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();
        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($genre->is_active) ? false : true;

        // definindo os dados a serem passados no body
        $name = 'name updated';
        $data = [
            'name' => $name,
            'is_active' => $isActiveAlternate,
            'categories_id' => $categoriesIds
        ];

        // fazendo o request
        sleep(1);
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($genre->id, $response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($isActiveAlternate, $response['data']['is_active']);
        $this->assertSame(Carbon::make($genre->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertNotSame(Carbon::make($genre->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $name,
            'is_active' => $isActiveAlternate,
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);
    }

    // testando o método update passando valores vazios
    public function testUpdateEmptyValues()
    {
        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'is_active' => '',
            'categories_id' => ''
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'is_active',
                'categories_id',
            ]
        ]);
    }

    // testando o método update, com falhas na validação
    public function testUpdateValidationFailure()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();
        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // inserindo um registro no bd
        $genre = GenreModel::factory()->create();

        // alterando o valor do isActive
        $isActiveAlternate = ($genre->is_active) ? false : true;

        // validando o atributo name
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'n',
            'is_active' => $isActiveAlternate,
            'categories_id' => $categoriesIds
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ]
        ]);

        // validando o atributo is_active
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'is_active' => 'fake',
            'categories_id' => $categoriesIds
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'is_active',
            ]
        ]);

        // validando o atributo categories_id
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'is_active' => true,
            'categories_id' => ['fake']
        ];

        // fazendo o request        
        $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'categories_id',
            ]
        ]);

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'n',
            'is_active' => 'fake',
            'categories_id' => ['fake']
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'is_active',
                'categories_id',
            ]
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
        $genre = GenreModel::factory()->create();

        // fazendo o request
        $response = $this->deleteJson("{$this->endpoint}/{$genre->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('genres', [
            'id' => $genre->id
        ]);
    }

    // testando o método destroy com categorias
    public function testDestroyWithCategories()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();
        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // definindo os dados a serem passados no body
        $name = 'name genre';
        $isActive = false;
        $data = [
            'name' => $name,
            'is_active' => $isActive,
            'categories_id' => $categoriesIds
        ];

        // inserindo o registro no bd
        $responseStore = $this->postJson($this->endpoint, $data);

        // fazendo o request
        $response = $this->deleteJson("{$this->endpoint}/{$responseStore['data']['id']}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('genres', [
            'id' => $responseStore['data']['id']
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);
    }
}
