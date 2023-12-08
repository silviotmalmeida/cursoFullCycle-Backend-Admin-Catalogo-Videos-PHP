<?php

namespace Tests\Feature\Core\UseCase\Category;

use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\InsertCategoryUseCase;
use Core\UseCase\DTO\Category\InsertCategory\InsertCategoryInputDto;
use Core\UseCase\DTO\Category\InsertCategory\InsertCategoryOutputDto;
use Tests\TestCase;

class InsertCategoryUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // dados de entrada
        $name = 'name cat';
        $description = 'description cat';
        $isActive = false;

        // criando o inputDto
        $inputDto = new InsertCategoryInputDto(
            name: $name,
            description: $description,
            isActive: $isActive
        );

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new InsertCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertCategoryOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($description, $responseUseCase->description);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);
    }
}
