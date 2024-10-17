<?php

namespace Tests\Feature\Core\UseCase\Video;

use App\Models\Video as VideoModel;
use App\Repositories\Eloquent\VideoEloquentRepository;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoInputDto;
use Core\UseCase\Video\Paginate\DTO\PaginateVideoOutputDto;
use Core\UseCase\Video\Paginate\PaginateVideoUseCase;
use Tests\TestCase;

class PaginateVideoUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução retornando lista vazia
    public function testExecuteReturningEmptyList()
    {
        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'ASC';
        $startPage = 1;
        $perPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateVideoInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new PaginateVideoUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateVideoOutputDto::class, $responseUseCase);
        $this->assertSame(0, $responseUseCase->total);
        $this->assertCount(0, $responseUseCase->items);
        $this->assertSame(1, $responseUseCase->current_page);
    }

    // função que testa o método de execução retornando lista existente
    public function testExecuteReturningExistingList()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = rand(40, 60);
        // inserindo múltiplos registros no bd
        VideoModel::factory()->count($qtd)->create();

        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'ASC';
        $startPage = 1;
        $perPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateVideoInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o repository
        $repository = new VideoEloquentRepository(new VideoModel());

        // criando o usecase
        $useCase = new PaginateVideoUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateVideoOutputDto::class, $responseUseCase);
        $this->assertCount($perPage, $responseUseCase->items);
        $this->assertSame($qtd, $responseUseCase->total);
        $this->assertSame($startPage, $responseUseCase->current_page);
    }
}
