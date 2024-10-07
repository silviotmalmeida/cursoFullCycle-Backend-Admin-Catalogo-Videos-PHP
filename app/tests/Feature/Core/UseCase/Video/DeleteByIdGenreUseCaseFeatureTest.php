<?php

namespace Tests\Feature\Core\UseCase\Video;

use App\Models\Video as VideoModel;
use App\Repositories\Eloquent\VideoEloquentRepository;
use Core\UseCase\Video\DeleteById\DeleteByIdVideoUseCase;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoInputDto;
use Core\UseCase\Video\DeleteById\DTO\DeleteByIdVideoOutputDto;
use Tests\TestCase;

class DeleteByIdVideoUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = VideoModel::factory()->create();

        // criando o inputDto
        $inputDto =  new DeleteByIdVideoInputDto($model->id);

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new DeleteByIdVideoUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(DeleteByIdVideoOutputDto::class, $responseUseCase);
        $this->assertSame(true, $responseUseCase->sucess);

        $this->assertSoftDeleted('videos', [
            'id' => $model->id
        ]);
    }
}
