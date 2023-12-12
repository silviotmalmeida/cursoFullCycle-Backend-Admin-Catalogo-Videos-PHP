<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryInputDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// definindo o controller
class CategoryController extends Controller
{
    // função responsável pela listagem das categorias
    public function index(Request $request, PaginateCategoryUseCase $usecase): AnonymousResourceCollection
    {
        // definindo o inputDto
        $inputDto = new PaginateCategoryInputDto(
            filter: $request->get('filter', ''),
            order: $request->get('order', 'DESC'),
            startPage: (int) $request->get('start_page', 1),
            itemsForPage: (int) $request->get('items_for_page', 15),
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
}
