<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Feature\App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Api\CastMemberController;
use App\Http\Requests\StoreCastMemberRequest;
use App\Http\Requests\UpdateCastMemberRequest;
use App\Models\CastMember as CastMemberModel;
use App\Repositories\Eloquent\CastMemberEloquentRepository;
use Core\Domain\Enum\CastMemberType;
use Core\UseCase\CastMember\DeleteByIdCastMemberUseCase;
use Core\UseCase\CastMember\FindByIdCastMemberUseCase;
use Core\UseCase\CastMember\InsertCastMemberUseCase;
use Core\UseCase\CastMember\PaginateCastMemberUseCase;
use Core\UseCase\CastMember\UpdateCastMemberUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CastMemberControllerFeatureTest extends TestCase
{

    // atributos
    protected CastMemberEloquentRepository $repository;

    // sobrescrevendo a função de preparação da classe mãe
    // é executada antes dos testes
    protected function setUp(): void
    {
        // reutilizando as instruções da classe mãe
        parent::setUp();
        // instanciando o repository
        $this->repository = new CastMemberEloquentRepository(new CastMemberModel());
    }

    // testando o método index
    public function testIndex()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        CastMemberModel::factory()->count($qtd)->create();

        // instanciando o usecase
        $usecase = new PaginateCastMemberUseCase($this->repository);

        // instanciando o controller
        $controller = new CastMemberController();
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
        $usecase = new InsertCastMemberUseCase($this->repository);

        // instanciando o controller
        $controller = new CastMemberController();
        // configurando o request com validação específica
        $storeRequest = new StoreCastMemberRequest();
        $storeRequest->headers->set('content-type', 'application/json');
        $storeRequest->setJson(new ParameterBag([
            'name' => 'name test',
            'type' => CastMemberType::ACTOR->value,
        ]));

        // executando o store
        $response = $controller->store($storeRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_CREATED, $response->status());

        $this->assertDatabaseHas('cast_members', [
            'name' => $storeRequest->json('name'),
            'type' => $storeRequest->json('type'),
        ]);
    }

    // testando o método show
    public function testShow()
    {
        // inserindo um registro no bd
        $castMember = CastMemberModel::factory()->create();

        // instanciando o usecase
        $usecase = new FindByIdCastMemberUseCase($this->repository);

        // instanciando o controller
        $controller = new CastMemberController();

        // executando o show
        $response = $controller->show($castMember->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
    }

    // testando o método update
    public function testUpdate()
    {
        // inserindo um registro no bd
        $castMember = CastMemberModel::factory()->create();

        // alterando o tipo
        $updatedType = ($castMember->type === 1) ? CastMemberType::ACTOR : CastMemberType::DIRECTOR;

        // instanciando o usecase
        $usecase = new UpdateCastMemberUseCase($this->repository);

        // instanciando o controller
        $controller = new CastMemberController();
        // configurando o request com validação específica
        $updateRequest = new UpdateCastMemberRequest();
        $updateRequest->headers->set('content-type', 'application/json');
        $updateRequest->setJson(new ParameterBag([
            'name' => 'name updated',
            'type' => $updatedType->value,
        ]));

        // executando o update
        $response = $controller->update($castMember->id, $updateRequest, $usecase);

        // verificando os dados
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());

        $this->assertDatabaseHas('cast_members', [
            'id' => $castMember->id,
            'name' => $updateRequest->json('name'),
            'type' => $updateRequest->json('type'),
        ]);
    }

    // testando o método destroy
    public function testDestroy()
    {
        // inserindo um registro no bd
        $castMember = CastMemberModel::factory()->create();

        // instanciando o usecase
        $usecase = new DeleteByIdCastMemberUseCase($this->repository);

        // instanciando o controller
        $controller = new CastMemberController();

        // executando o destroy
        $response = $controller->destroy($castMember->id, $usecase);

        // verificando os dados
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->status());

        $this->assertSoftDeleted('cast_members', [
            'id' => $castMember->id
        ]);
    }
}
