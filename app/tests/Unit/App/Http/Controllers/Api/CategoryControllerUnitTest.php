<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\App\Http\Controllers\Api;

// importações
use PHPUnit\Framework\TestCase;
use App\Http\Controllers\Api\CategoryController;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryOutputDto;
use Illuminate\Http\Request;
use Mockery;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CategoryControllerUnitTest extends TestCase
{
    // testando o método index
    public function testIndex()
    {
        // definindo o mock do request
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('get')->andReturn('test');

        // definindo o mock do outputDTO
        $mockOutputDto = Mockery::mock(PaginateCategoryOutputDto::class, [
            [], 1, 1, 1, 1, 1, 1, 1
        ]);

        // definindo o mock do usecase
        $mockUsecase = Mockery::mock(PaginateCategoryUseCase::class);
        $mockUsecase->shouldReceive('execute')->andReturn($mockOutputDto);

        // instanciando o controller
        $controller = new CategoryController();
        // executando o index
        $response = $controller->index($mockRequest, $mockUsecase);

        // verificando os dados
        $this->assertIsObject($response->resource);
        $this->assertArrayHasKey('meta', $response->additional);

        // definindo o spy do usecase
        $mockUsecase = Mockery::spy(PaginateCategoryUseCase::class);
        $mockUsecase->shouldReceive('execute')->andReturn($mockOutputDto);

        // executando o index
        $response = $controller->index($mockRequest, $mockUsecase);

        // verificando a chamada do método
        $mockUsecase->shouldHaveReceived('execute');

        // encerrando o mockery
        Mockery::close();
    }
}
