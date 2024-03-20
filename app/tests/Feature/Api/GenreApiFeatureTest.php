<?php

namespace Tests\Feature\Api;

use App\Models\Genre as GenreModel;
use Illuminate\Http\Response;
use Tests\TestCase;

class GenreApiFeatureTest extends TestCase
{
    // atributos
    protected $endpoint = '/api/genres';

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
    }

    // // testando o método show com id inexistente
    // public function testShowNotFound()
    // {
    //     // fazendo o request
    //     $response = $this->getJson("{$this->endpoint}/fake_id");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NOT_FOUND);
    // }

    // // testando o método show
    // public function testShow()
    // {
    //     // inserindo um registro no bd
    //     $genre = GenreModel::factory()->create();

    //     // fazendo o request
    //     $response = $this->getJson("{$this->endpoint}/{$genre->id}");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_OK);
    //     $response->assertJsonStructure([
    //         'data' => [
    //             'id',
    //             'name',
    //             'description',
    //             'is_active',
    //             'created_at',
    //             'updated_at',
    //         ]
    //     ]);
    //     $this->assertSame($genre->id, $response['data']['id']);
    //     $this->assertSame($genre->name, $response['data']['name']);
    //     $this->assertSame($genre->description, $response['data']['description']);
    //     $this->assertSame($genre->is_active, $response['data']['is_active']);
    //     $this->assertEquals($genre->created_at, $response['data']['created_at']);
    //     $this->assertEquals($genre->updated_at, $response['data']['updated_at']);
    // }

    // // testando o método store sem passagem dos atributos para criação
    // public function testStoreWithoutData()
    // {
    //     // definindo os dados a serem passados no body
    //     $data = [];

    //     // fazendo o request
    //     $response = $this->postJson($this->endpoint, $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    //     $response->assertJsonStructure([
    //         'message',
    //         'errors' => [
    //             'name',
    //         ]
    //     ]);
    // }

    // // testando o método store
    // public function testStore()
    // {
    //     // definindo os dados a serem passados no body
    //     $name = 'name test';
    //     $description = 'desc test';
    //     $isActive = false;
    //     $data = [
    //         'name' => $name,
    //         'description' => $description,
    //         'is_active' => $isActive,
    //     ];

    //     // fazendo o request
    //     $response = $this->postJson($this->endpoint, $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_CREATED);
    //     $response->assertJsonStructure([
    //         'data' => [
    //             'id',
    //             'name',
    //             'description',
    //             'is_active',
    //             'created_at',
    //             'updated_at',
    //         ]
    //     ]);
    //     $this->assertNotEmpty($response['data']['id']);
    //     $this->assertSame($name, $response['data']['name']);
    //     $this->assertSame($description, $response['data']['description']);
    //     $this->assertSame($isActive, $response['data']['is_active']);
    //     $this->assertNotEmpty($response['data']['created_at']);
    //     $this->assertNotEmpty($response['data']['updated_at']);

    //     $this->assertDatabaseHas('categories', [
    //         'name' => $name,
    //         'description' => $description,
    //         'is_active' => $isActive,
    //     ]);
    // }

    // // testando o método update com id inexistente
    // public function testUpdateNotFound()
    // {
    //     // definindo os dados a serem passados no body        
    //     $name = 'name updated';
    //     $description = 'desc updated';
    //     $isActive = false;
    //     $data = [
    //         'name' => $name,
    //         'description' => $description,
    //         'is_active' => $isActive,
    //     ];

    //     // fazendo o request
    //     $response = $this->putJson("{$this->endpoint}/fake_id", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NOT_FOUND);
    // }

    // // testando o método update
    // public function testUpdate()
    // {
    //     // inserindo um registro no bd
    //     $genre = GenreModel::factory()->create();

    //     // alterando o valor do isActive
    //     $isActiveAlternate = ($genre->is_active) ? false : true;

    //     // definindo os dados a serem passados no body
    //     $name = 'name updated';
    //     $description = 'desc updated';
    //     $data = [
    //         'name' => $name,
    //         'description' => $description,
    //         'is_active' => $isActiveAlternate,
    //     ];

    //     // fazendo o request
    //     $response = $this->putJson("{$this->endpoint}/{$genre->id}", $data);

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_OK);
    //     $response->assertJsonStructure([
    //         'data' => [
    //             'id',
    //             'name',
    //             'description',
    //             'is_active',
    //             'created_at',
    //             'updated_at',
    //         ]
    //     ]);
    //     $this->assertSame($genre->id, $response['data']['id']);
    //     $this->assertSame($name, $response['data']['name']);
    //     $this->assertSame($description, $response['data']['description']);
    //     $this->assertSame($isActiveAlternate, $response['data']['is_active']);
    //     $this->assertNotSame($genre->created_at, $response['data']['created_at']);
    //     $this->assertNotSame($genre->updated_at, $response['data']['updated_at']);

    //     $this->assertDatabaseHas('categories', [
    //         'id' => $genre->id,
    //         'name' => $name,
    //         'description' => $description,
    //         'is_active' => $isActiveAlternate,
    //     ]);
    // }

    // // testando o método destroy com id inexistente
    // public function testDestroyNotFound()
    // {
    //     // fazendo o request
    //     $response = $this->deleteJson("{$this->endpoint}/fake_id");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NOT_FOUND);
    // }

    // // testando o método destroy
    // public function testDestroy()
    // {
    //     // inserindo um registro no bd
    //     $genre = GenreModel::factory()->create();

    //     // fazendo o request
    //     $response = $this->deleteJson("{$this->endpoint}/{$genre->id}");

    //     // verificando os dados
    //     $response->assertStatus(Response::HTTP_NO_CONTENT);

    //     $this->assertSoftDeleted('categories', [
    //         'id' => $genre->id
    //     ]);
    // }
}
