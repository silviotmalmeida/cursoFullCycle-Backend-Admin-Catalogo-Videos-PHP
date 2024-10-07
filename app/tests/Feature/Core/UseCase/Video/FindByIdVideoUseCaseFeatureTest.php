<?php

namespace Tests\Feature\Core\UseCase\Video;

use App\Models\Video as VideoModel;
use App\Repositories\Eloquent\VideoEloquentRepository;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoInputDto;
use Core\UseCase\Video\FindById\DTO\FindByIdVideoOutputDto;
use Core\UseCase\Video\FindById\FindByIdVideoUseCase;
use Tests\TestCase;

class FindByIdVideoUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();

        // criando o inputDto
        $inputDto =  new FindByIdVideoInputDto($model->id);

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new FindByIdVideoUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(FindByIdVideoOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertSame($model->title, $responseUseCase->title);
        $this->assertSame($model->description, $responseUseCase->description);
        $this->assertSame($model->year_launched, $responseUseCase->yearLaunched);
        $this->assertSame($model->duration, $responseUseCase->duration);
        $this->assertSame($model->rating, $responseUseCase->rating->value);
        $this->assertEquals($model->created_at, $responseUseCase->created_at);
        $this->assertEquals($model->updated_at, $responseUseCase->updated_at);  
    }
}
