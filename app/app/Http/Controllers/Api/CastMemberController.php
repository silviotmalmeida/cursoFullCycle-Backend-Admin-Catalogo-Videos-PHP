<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCastMemberRequest;
use App\Http\Requests\UpdateCastMemberRequest;
use App\Http\Resources\CastMemberResource;
use Core\UseCase\CastMember\DeleteByIdCastMemberUseCase;
use Core\UseCase\CastMember\FindByIdCastMemberUseCase;
use Core\UseCase\CastMember\InsertCastMemberUseCase;
use Core\UseCase\CastMember\PaginateCastMemberUseCase;
use Core\UseCase\CastMember\UpdateCastMemberUseCase;
use Core\UseCase\DTO\CastMember\DeleteByIdCastMember\DeleteByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\FindByIdCastMember\FindByIdCastMemberInputDto;
use Core\UseCase\DTO\CastMember\InsertCastMember\InsertCastMemberInputDto;
use Core\UseCase\DTO\CastMember\PaginateCastMember\PaginateCastMemberInputDto;
use Core\UseCase\DTO\CastMember\UpdateCastMember\UpdateCastMemberInputDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

// definindo o controller
class CastMemberController extends Controller
{
    // função responsável pela listagem dos castMembers
    public function index(Request $request, PaginateCastMemberUseCase $usecase): AnonymousResourceCollection
    {
        // definindo o inputDto com os dados a partir da url de request
        $inputDto = new PaginateCastMemberInputDto(
            filter: $request->get('filter', ''),
            order: $request->get('order', 'ASC'),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15),
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = CastMemberResource::collection(collect($outputDto->items))
            ->additional(['meta' => [
                'total' => $outputDto->total,
                'last_page' => $outputDto->last_page,
                'first_page' => $outputDto->first_page,
                'current_page' => $outputDto->current_page,
                'per_page' => $outputDto->per_page,
                'to' => $outputDto->to,
                'from' => $outputDto->from,
            ]]);

        return $response;
    }

    // função responsável pela criação dos castMembers
    public function store(StoreCastMemberRequest $request, InsertCastMemberUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new InsertCastMemberInputDto(
            name: $request->name,
            type: $request->type,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CastMemberResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    // função responsável pela exibição dos castMembers
    public function show(string $id, FindByIdCastMemberUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new FindByIdCastMemberInputDto(
            id: $id,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CastMemberResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela atualização dos castMembers
    public function update(string $id, UpdateCastMemberRequest $request, UpdateCastMemberUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new UpdateCastMemberInputDto(
            id: $id,
            name: $request->name ?? null,
            type: $request->type ?? null,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CastMemberResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela exclusão dos castMembers
    public function destroy(string $id, DeleteByIdCastMemberUseCase $usecase): Response
    {
        // definindo o inputDto
        $inputDto = new DeleteByIdCastMemberInputDto(
            id: $id,
        );

        // executando o usecase
        $usecase->execute($inputDto);

        // organizando a response
        $response = response()->noContent();

        return $response;
    }
}
