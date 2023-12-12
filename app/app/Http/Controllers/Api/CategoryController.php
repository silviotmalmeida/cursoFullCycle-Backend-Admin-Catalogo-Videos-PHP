<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Core\UseCase\Category\DeleteByIdCategoryUseCase;
use Core\UseCase\Category\FindByIdCategoryUseCase;
use Core\UseCase\Category\InsertCategoryUseCase;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\Category\UpdateCategoryUseCase;
use Core\UseCase\DTO\Category\DeleteByIdCategory\DeleteByIdCategoryInputDto;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryInputDto;
use Core\UseCase\DTO\Category\InsertCategory\InsertCategoryInputDto;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryInputDto;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryInputDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

// definindo o controller
class CategoryController extends Controller
{
    // função responsável pela listagem das categorias
    public function index(Request $request, PaginateCategoryUseCase $usecase): AnonymousResourceCollection
    {
        // definindo o inputDto
        $inputDto = new PaginateCategoryInputDto(
            filter: $request->filter ?? '',
            order: $request->order ?? 'DESC',
            startPage: (int) $request->start_page ?? 1,
            itemsForPage: (int) $request->items_for_page ?? 15,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = CategoryResource::collection(collect($outputDto->items))
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
    public function store(StoreCategoryRequest $request, InsertCategoryUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new InsertCategoryInputDto(
            name: $request->name,
            description: $request->description ?? '',
            isActive: (bool) $request->is_active ?? true,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CategoryResource(collect($outputDto)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    // função responsável pela exibição das categorias
    public function show(Request $request, FindByIdCategoryUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new FindByIdCategoryInputDto(
            id: $request->id ?? '',
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CategoryResource(collect($outputDto)))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela atualização das categorias
    public function update(UpdateCategoryRequest $request, UpdateCategoryUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new UpdateCategoryInputDto(
            id: $request->id,
            name: $request->name ?? '',
            description: $request->description ?? '',
            isActive: $request->is_active ?? '',
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new CategoryResource(collect($outputDto)))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela exclusão das categorias
    public function destroy(Request $request, DeleteByIdCategoryUseCase $usecase): Response
    {
        // definindo o inputDto
        $inputDto = new DeleteByIdCategoryInputDto(
            id: $request->id ?? '',
        );

        // executando o usecase
        $usecase->execute($inputDto);

        // organizando a response
        $response = response()->noContent();

        return $response;
    }
}
