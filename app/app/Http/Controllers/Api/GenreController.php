<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Http\Resources\GenreResource;
use Core\UseCase\Genre\DeleteByIdGenreUseCase;
use Core\UseCase\Genre\FindByIdGenreUseCase;
use Core\UseCase\Genre\InsertGenreUseCase;
use Core\UseCase\Genre\PaginateGenreUseCase;
use Core\UseCase\Genre\UpdateGenreUseCase;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreInputDto;
use Core\UseCase\DTO\Genre\FindByIdGenre\FindByIdGenreInputDto;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreInputDto;
use Core\UseCase\DTO\Genre\PaginateGenre\PaginateGenreInputDto;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreInputDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

// definindo o controller
class GenreController extends Controller
{
    // função responsável pela listagem das categorias
    public function index(Request $request, PaginateGenreUseCase $usecase): AnonymousResourceCollection
    {
        // definindo o inputDto com os dados a partir da url de request
        $inputDto = new PaginateGenreInputDto(
            filter: $request->get('filter', ''),
            order: $request->get('order', 'ASC'),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15),
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = GenreResource::collection(collect($outputDto->items))
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

    // função responsável pela criação das categorias
    public function store(StoreGenreRequest $request, InsertGenreUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new InsertGenreInputDto(
            name: $request->name,
            isActive: (bool) $request->is_active ?? true,
            categoriesId: $request->categories_id ?? []
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new GenreResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    // função responsável pela exibição das categorias
    public function show(string $id, FindByIdGenreUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new FindByIdGenreInputDto(
            id: $id,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new GenreResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela atualização das categorias
    public function update(string $id, UpdateGenreRequest $request, UpdateGenreUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new UpdateGenreInputDto(
            id: $id,
            name: $request->name ?? null,
            isActive: $request->is_active ?? null,
            categoriesId: $request->categories_id ?? null
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new GenreResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela exclusão das categorias
    public function destroy(string $id, DeleteByIdGenreUseCase $usecase): Response
    {
        // definindo o inputDto
        $inputDto = new DeleteByIdGenreInputDto(
            id: $id,
        );

        // executando o usecase
        $usecase->execute($inputDto);

        // organizando a response
        $response = response()->noContent();

        return $response;
    }
}
