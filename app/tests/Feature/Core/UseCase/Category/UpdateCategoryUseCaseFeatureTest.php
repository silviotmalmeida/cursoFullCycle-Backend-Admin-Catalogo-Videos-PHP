<?php

namespace Tests\Feature\Core\UseCase\Category;

use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\UpdateCategoryUseCase;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryInputDto;
use Core\UseCase\DTO\Category\UpdateCategory\UpdateCategoryOutputDto;
use Tests\TestCase;

class UpdateCategoryUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = CategoryModel::factory()->create();
        sleep(1);

        // criando o inputDto
        $inputDto =  new UpdateCategoryInputDto(
            id: $model->id,
            name: "updated name",
            description: "updated description"
        );

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateCategoryOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotSame($model->name, $responseUseCase->name);
        $this->assertNotSame($model->description, $responseUseCase->description);
        $this->assertSame($inputDto->name, $responseUseCase->name);
        $this->assertSame($inputDto->description, $responseUseCase->description);
        $this->assertSame($model->is_active, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        $this->assertDatabaseHas('categories', [
            'id' => $model->id,
            'name' => $inputDto->name,
            'description' => $inputDto->description,
            'is_active' => $model->is_active
        ]);
    }
}
