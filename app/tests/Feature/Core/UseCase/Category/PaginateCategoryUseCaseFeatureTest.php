<?php

namespace Tests\Feature\Core\UseCase\Category;

use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryInputDto;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryOutputDto;
use Tests\TestCase;

class PaginateCategoryUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução retornando lista vazia
    public function testExecuteReturningEmptyList()
    {
        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $itemsForPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateCategoryInputDto(
            $filter,
            $order,
            $startPage,
            $itemsForPage,
        );

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateCategoryOutputDto::class, $responseUseCase);
        $this->assertSame(0, $responseUseCase->total);
        $this->assertCount(0, $responseUseCase->items);
        $this->assertSame(1, $responseUseCase->current_page);
    }

    // função que testa o método de execução retornando lista existente
    public function testExecuteReturningExistingList()
    {
        // definindo a quantidade de registros a serem criados
        $qtd = 50;
        // inserindo múltiplos registros no bd
        CategoryModel::factory()->count($qtd)->create();

        // definindo os atributos a serem utilizados no inputDto
        $filter = '';
        $order = 'DESC';
        $startPage = 1;
        $itemsForPage = 10;

        // criando o inputDto
        $inputDto =  new PaginateCategoryInputDto(
            $filter,
            $order,
            $startPage,
            $itemsForPage,
        );

        // criando o repository
        $repository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($repository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateCategoryOutputDto::class, $responseUseCase);
        $this->assertCount($itemsForPage, $responseUseCase->items);
        $this->assertSame($qtd, $responseUseCase->total);
        $this->assertSame($startPage, $responseUseCase->current_page);
    }
}
