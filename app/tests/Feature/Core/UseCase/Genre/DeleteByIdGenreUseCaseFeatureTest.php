<?php

namespace Tests\Feature\Core\UseCase\Genre;

use App\Models\Genre as GenreModel;
use App\Repositories\Eloquent\GenreEloquentRepository;
use Core\UseCase\Genre\DeleteByIdGenreUseCase;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreInputDto;
use Core\UseCase\DTO\Genre\DeleteByIdGenre\DeleteByIdGenreOutputDto;
use Tests\TestCase;

class DeleteByIdGenreUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = GenreModel::factory()->create();

        // criando o inputDto
        $inputDto =  new DeleteByIdGenreInputDto($model->id);

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o usecase
        $useCase = new DeleteByIdGenreUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdGenreOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        $this->assertSoftDeleted('genres', [
            'id' => $model->id
        ]);
    }
}
