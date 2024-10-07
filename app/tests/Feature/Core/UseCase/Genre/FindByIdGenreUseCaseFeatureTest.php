<?php

namespace Tests\Feature\Core\UseCase\Genre;

use App\Models\Genre as GenreModel;
use App\Repositories\Eloquent\GenreEloquentRepository;
use Core\UseCase\Genre\FindByIdGenreUseCase;
use Core\UseCase\DTO\Genre\FindByIdGenre\FindByIdGenreInputDto;
use Core\UseCase\DTO\Genre\FindByIdGenre\FindByIdGenreOutputDto;
use Tests\TestCase;

class FindByIdGenreUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = GenreModel::factory()->create();

        // criando o inputDto
        $inputDto =  new FindByIdGenreInputDto($model->id);

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o usecase
        $useCase = new FindByIdGenreUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdGenreOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertSame($model->name, $responseUseCase->name);
        $this->assertSame($model->is_active, $responseUseCase->is_active);
        $this->assertEquals($model->created_at, $responseUseCase->created_at);
        $this->assertEquals($model->updated_at, $responseUseCase->updated_at); 
    }
}
