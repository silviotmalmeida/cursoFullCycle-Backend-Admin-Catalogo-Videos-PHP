<?php

namespace Tests\Feature\Core\UseCase\Category;

use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\FindByIdCategoryUseCase;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryInputDto;
use Core\UseCase\DTO\Category\FindByIdCategory\FindByIdCategoryOutputDto;
use Tests\TestCase;

class FindByIdCategoryUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();

        // criando o inputDto
        $inputDto =  new FindByIdCategoryInputDto($model->id);

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new FindByIdCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdCategoryOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertSame($model->name, $responseUseCase->name);
        $this->assertSame($model->description, $responseUseCase->description);
        $this->assertSame($model->is_active, $responseUseCase->is_active);
        $this->assertEquals($model->created_at, $responseUseCase->created_at);
        $this->assertEquals($model->updated_at, $responseUseCase->updated_at); 
    }
}
