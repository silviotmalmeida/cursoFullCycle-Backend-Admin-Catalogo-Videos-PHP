<?php

namespace Tests\Feature\Api;

use App\Models\CastMember as CastMemberModel;
use Carbon\Carbon;
use Core\Domain\Enum\CastMemberType;
use Illuminate\Http\Response;
use Tests\TestCase;

class CastMemberApiFeatureTest extends TestCase
{
    // atributos
    private $endpoint = '/api/cast_members';

    // testando o método index com retorno vazio
    public function testIndexWithNoCastMembers()
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
        CastMemberModel::factory()->count($total)->create();

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
        $castMember = CastMemberModel::factory()->create();

        // fazendo o request
        $response = $this->getJson("{$this->endpoint}/{$castMember->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($castMember->id, $response['data']['id']);
        $this->assertSame($castMember->name, $response['data']['name']);
        $this->assertSame($castMember->type, $response['data']['type']);
        $this->assertSame(Carbon::make($castMember->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertSame(Carbon::make($castMember->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);
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
        // array de valores possíveis do type
        $typeValues = array_column(CastMemberType::cases(), 'value');
        $type = $typeValues[array_rand($typeValues)];
        $data = [
            'name' => $name,
            'type' => $type,
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertNotEmpty($response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($type, $response['data']['type']);
        $this->assertNotEmpty($response['data']['created_at']);
        $this->assertNotEmpty($response['data']['updated_at']);

        $this->assertDatabaseHas('cast_members', [
            'name' => $name,
            'type' => $type,
        ]);
    }

    // testando o método store, com falhas na validação
    public function testStoreValidationFailure()
    {
        // validando o atributo name
        // definindo os dados a serem passados no body
        // array de valores possíveis do type
        $typeValues = array_column(CastMemberType::cases(), 'value');
        $type = $typeValues[array_rand($typeValues)];
        $data = [
            'name' => '',
            'type' => $type,
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

        // validando o atributo type
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'type' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'type',
            ]
        ]);

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'type' => 'fake'
        ];

        // fazendo o request
        $response = $this->postJson($this->endpoint, $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'type',
            ]
        ]);
    }

    // testando o método update com id inexistente
    public function testUpdateNotFound()
    {
        // definindo os dados a serem passados no body
        // array de valores possíveis do type
        $typeValues = array_column(CastMemberType::cases(), 'value');
        $type = $typeValues[array_rand($typeValues)];
        $name = 'name updated';
        $data = [
            'name' => $name,
            'type' => $type,
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
        $castMember = CastMemberModel::factory()->create();

        // alterando o tipo
        $updatedType = ($castMember->type === 1) ? CastMemberType::ACTOR : CastMemberType::DIRECTOR;

        // definindo os dados a serem passados no body
        $name = 'name updated';
        $data = [
            'name' => $name,
            'type' => $updatedType->value,
        ];

        // fazendo o request
        sleep(1);
        $response = $this->putJson("{$this->endpoint}/{$castMember->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'type',
                'created_at',
                'updated_at',
            ]
        ]);
        $this->assertSame($castMember->id, $response['data']['id']);
        $this->assertSame($name, $response['data']['name']);
        $this->assertSame($updatedType->value, $response['data']['type']);
        $this->assertSame(Carbon::make($castMember->created_at)->format('Y-m-d H:i:s'), $response['data']['created_at']);
        $this->assertNotSame(Carbon::make($castMember->updated_at)->format('Y-m-d H:i:s'), $response['data']['updated_at']);

        $this->assertDatabaseHas('cast_members', [
            'id' => $castMember->id,
            'name' => $name,
            'type' => $updatedType,
        ]);
    }

    // testando o método update passando valores vazios
    public function testUpdateEmptyValues()
    {
        // inserindo um registro no bd
        $castMember = CastMemberModel::factory()->create();

        // definindo os dados a serem passados no body
        $data = [
            'name' => '',
            'type' => '',
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$castMember->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'type',
            ]
        ]);
    }

    // testando o método update, com falhas na validação
    public function testUpdateValidationFailure()
    {
        // inserindo um registro no bd
        $castMember = CastMemberModel::factory()->create();

        // alterando o tipo
        $updatedType = ($castMember->type === 1) ? CastMemberType::ACTOR : CastMemberType::DIRECTOR;

        // validando o atributo name
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'n',
            'type' => $updatedType->value,
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$castMember->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ]
        ]);

        // validando o atributo type
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'name',
            'type' => 'fake'
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$castMember->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'type',
            ]
        ]);

        // validando todos os atributos
        // definindo os dados a serem passados no body
        $data = [
            'name' => 'n',
            'type' => 'fake'
        ];

        // fazendo o request
        $response = $this->putJson("{$this->endpoint}/{$castMember->id}", $data);

        // verificando os dados
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'type',
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
        $castMember = CastMemberModel::factory()->create();

        // fazendo o request
        $response = $this->deleteJson("{$this->endpoint}/{$castMember->id}");

        // verificando os dados
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('cast_members', [
            'id' => $castMember->id
        ]);
    }
}
