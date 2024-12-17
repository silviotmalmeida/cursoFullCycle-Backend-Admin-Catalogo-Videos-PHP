<?php

namespace Tests\Feature\Api;

use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use Tests\Traits\DisableAuthMiddlewareTrait;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class GenreApiFeatureTest extends TestCase
{
    // atributos
    private $endpoint = '/api/genres';

    // aplicando a trait para desativar os middlewares de autenticação nos testes
    use DisableAuthMiddlewareTrait;

    // testando o método index com retorno vazio
    public function testIndexWithNoGenres()
    {
        // fazendo o request
        $response = $this->getJson($this->endpoint);

        // validando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(0, 'data');
    }

    // provedor de dados do testIndex
    public function dataProviderTestIndex(): array
    {
        return [
            [
                'qtd' => 25,
                'page' => 1,
                'perPage' => 10,
                'items' => 10,
                'filter' => '',
            ],
            [
                'qtd' => 25,
                'page' => 2,
                'perPage' => 10,
                'items' => 10,
                'filter' => '',
            ],
            [
                'qtd' => 25,
                'page' => 3,
                'perPage' => 10,
                'items' => 5,
                'filter' => '',
            ],
            [
                'qtd' => 26,
                'page' => 1,
                'perPage' => 10,
                'items' => 10,
                'filter' => 'filtro aplicado',
            ],
        ];
    }

    // testando o método index
    // utiliza o dataProvider dataProviderTestIndex
    /**
     * @dataProvider dataProviderTestIndex
     */
    public function testIndex(
        int $qtd,
        int $page,
        int $perPage,
        int $items,
        string $filter
    ) {
        // inserindo múltiplos registros no bd
        // se existirem filtros, metade dos registros serão filtrados
        if ($filter) {
            GenreModel::factory()->count($qtd / 2)->create();
            GenreModel::factory()->count($qtd / 2)->create(
                [
                    'name' => $filter
                ]
            );
            // ajustando a quantidade de registros retornados
            $qtd = $qtd / 2;
        }
        // senão, cria registros aleatórios
        else {
            GenreModel::factory()->count($qtd)->create();
        }

        // definindo as métricas
        $lastPage = (int) (ceil($qtd / $perPage));
        $firstPage = 1;
        $to = ($page - 1) * ($perPage) + 1;
        $from = $qtd > ($page * $perPage) ? ($page * $perPage) : $qtd;

        // organizando os parâmetros a serem considerados
        $params = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
            'order'  => 'ASC',
            'filter' => $filter
        ]);

        // fazendo o request
        $response = $this->getJson("$this->endpoint?$params");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount($items, $response['data']);
        $this->assertSame($qtd, $response['meta']['total']);
        $this->assertSame($perPage, $response['meta']['per_page']);
        $this->assertSame($lastPage, $response['meta']['last_page']);
        $this->assertSame($firstPage, $response['meta']['first_page']);
        $this->assertSame($page, $response['meta']['current_page']);
        $this->assertSame($to, $response['meta']['to']);
        $this->assertSame($from, $response['meta']['from']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'is_active',
                    'categories_id',
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
                'categories_id',
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
                'categories_id',
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
                'categories_id',
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

        // verificando relacionamentos
        $this->assertDatabaseCount('category_genre', $qtd);
        $this->assertCount($qtd, $response['data']['categories_id']);
        $this->assertEquals($categoriesIds, $response['data']['categories_id']);

        // verificando o relacionamento a partir de category
        foreach ($categoriesIds as $categoryId) {
            $this->assertDatabaseHas('category_genre', [
                'genre_id' => $response['data']['id'],
                'category_id' => $categoryId,
            ]);
            $categoryModel = CategoryModel::find($categoryId);
            $this->assertCount(1, $categoryModel->genres);
        }
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
                'categories_id',
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
                'categories_id',
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
