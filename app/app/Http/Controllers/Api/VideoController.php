<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Http\Controllers\Api;

// importações
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use Core\UseCase\Video\DeleteById\DeleteByIdVideoUseCase;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoInputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoInputDto;
use Core\UseCase\Video\FindById\FindByIdVideoUseCase;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use Core\UseCase\Video\Insert\InsertVideoUseCase;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoInputDto;
use Core\UseCase\Video\Paginate\PaginateVideoUseCase;
use Core\UseCase\Video\Update\DTO\UpdateVideoInputDto;
use Core\UseCase\Video\Update\UpdateVideoUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

// definindo o controller
class VideoController extends Controller
{
    // função responsável pela listagem das categorias
    public function index(Request $request, PaginateVideoUseCase $usecase): AnonymousResourceCollection
    {
        // definindo o inputDto com os dados a partir da url de request
        $inputDto = new PaginateVideoInputDto(
            filter: $request->get('filter', ''),
            order: $request->get('order', 'DESC'),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15),
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = VideoResource::collection(collect($outputDto->items))
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

    // função responsável pela exibição das categorias
    public function show(string $id, FindByIdVideoUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new FindByIdVideoInputDto(
            id: $id,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new VideoResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela criação das categorias
    public function store(StoreVideoRequest $request, InsertVideoUseCase $usecase): JsonResponse
    {

        // definindo o inputDto
        $inputDto = new InsertVideoInputDto(
            title: $request->title,
            description: $request->description,
            yearLaunched: $request->year_launched,
            duration: $request->duration,
            opened: (bool) $request->opened ?? false,
            rating: $request->rating,
            categoriesId: $request->categories_id ?? [],
            genresId: $request->genres_id ?? [],
            castMembersId: $request->cast_members_id ?? [],
            thumbFile: $request->thumbfile ?? null,
            thumbHalf: $request->thumbhalf ?? null,
            bannerFile: $request->bannerfile ?? null,
            trailerFile: $request->trailerfile ?? null,
            videoFile: $request->videofile ?? null,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new VideoResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    // função responsável pela atualização das categorias
    public function update(string $id, UpdateVideoRequest $request, UpdateVideoUseCase $usecase): JsonResponse
    {
        // definindo o inputDto
        $inputDto = new UpdateVideoInputDto(
            id: $id,
            title: $request->title,
            description: $request->description,
            yearLaunched: $request->year_launched,
            duration: $request->duration,
            opened: (bool) $request->opened ?? false,
            rating: $request->rating,
            categoriesId: $request->categories_id ?? [],
            genresId: $request->genres_id ?? [],
            castMembersId: $request->cast_members_id ?? [],
            thumbFile: $request->thumbfile ?? null,
            thumbHalf: $request->thumbhalf ?? null,
            bannerFile: $request->bannerfile ?? null,
            trailerFile: $request->trailerfile ?? null,
            videoFile: $request->videofile ?? null,
        );

        // executando o usecase
        $outputDto = $usecase->execute($inputDto);

        // organizando a response
        $response = (new VideoResource($outputDto))
            ->response()
            ->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    // função responsável pela exclusão das categorias
    public function destroy(string $id, DeleteByIdVideoUseCase $usecase): Response
    {
        // definindo o inputDto
        $inputDto = new DeleteByIdVideoInputDto(
            id: $id,
        );

        // executando o usecase
        $usecase->execute($inputDto);

        // organizando a response
        $response = response()->noContent();

        return $response;
    }
}
