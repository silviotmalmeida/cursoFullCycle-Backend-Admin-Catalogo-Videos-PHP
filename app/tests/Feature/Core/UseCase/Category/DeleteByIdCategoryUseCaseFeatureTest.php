<?php

namespace Tests\Feature\Core\UseCase\Category;

use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\DeleteByIdCategoryUseCase;
use Core\UseCase\DTO\Category\DeleteByIdCategory\DeleteByIdCategoryInputDto;
use Core\UseCase\DTO\Category\DeleteByIdCategory\DeleteByIdCategoryOutputDto;
use Tests\TestCase;

class DeleteByIdCategoryUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();

        // criando o inputDto
        $inputDto =  new DeleteByIdCategoryInputDto($model->id);

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new DeleteByIdCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        $this->assertSoftDeleted($model);
    }
}
