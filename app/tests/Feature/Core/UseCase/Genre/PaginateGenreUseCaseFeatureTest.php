<?php

namespace Tests\Feature\Core\UseCase\Genre;

use App\Models\Genre as GenreModel;
use App\Repositories\Eloquent\GenreEloquentRepository;
use Core\UseCase\Genre\PaginateGenreUseCase;
use Core\UseCase\DTO\Genre\PaginateGenre\PaginateGenreInputDto;
use Core\UseCase\DTO\Genre\PaginateGenre\PaginateGenreOutputDto;
use Tests\TestCase;

class PaginateGenreUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução retornando lista vazia
    public function testExecuteReturningEmptyList()
    {
        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $perPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateGenreInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o usecase
        $useCase = new PaginateGenreUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateGenreOutputDto::class, $responseUseCase);
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
        GenreModel::factory()->count($qtd)->create();

        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $perPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateGenreInputDto(
            $filter,
            $order,
            $startPage,
            $perPage,
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o usecase
        $useCase = new PaginateGenreUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateGenreOutputDto::class, $responseUseCase);
        $this->assertCount($perPage, $responseUseCase->items);
        $this->assertSame($qtd, $responseUseCase->total);
        $this->assertSame($startPage, $responseUseCase->current_page);
    }
}
