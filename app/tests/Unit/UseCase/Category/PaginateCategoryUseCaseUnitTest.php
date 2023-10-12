<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\UseCase\Category;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Repository\CategoryRepositoryInterface;
use Core\Domain\Repository\PaginationInterface;
use Core\UseCase\Category\PaginateCategoryUseCase;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryInputDto;
use Core\UseCase\DTO\Category\PaginateCategory\PaginateCategoryOutputDto;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class PaginateCategoryUseCaseUnitTest extends TestCase
{
    // função que testa o método de execução retornando lista vazia
    public function testExecuteReturningEmptyList()
    {
        // definindo os atributos a serem utilizados nos mocks
        $filter = '';
        $order = 'DESC';
        $page = 1;
        $itemsForPage = 10;
        $items = [];
        $total = 0;
        $lastPage = 1;
        $firstPage = 1;
        $currentPage = $page;
        $perPage = $itemsForPage;
        $to = 1;
        $from = 1;

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(PaginateCategoryInputDto::class, [
            $filter,
            $order,
            $page,
            $itemsForPage,
        ]);

        // criando o mock do Pagination
        $mockPagination = Mockery::mock(stdClass::class, PaginationInterface::class);
        $mockPagination->shouldReceive('items')->andReturn($items); //definindo o retorno do items()
        $mockPagination->shouldReceive('total')->andReturn($total); //definindo o retorno do total()
        $mockPagination->shouldReceive('lastPage')->andReturn($lastPage); //definindo o retorno do lastPage()
        $mockPagination->shouldReceive('firstPage')->andReturn($firstPage); //definindo o retorno do firstPage()
        $mockPagination->shouldReceive('currentPage')->andReturn($currentPage); //definindo o retorno do currentPage()
        $mockPagination->shouldReceive('perPage')->andReturn($perPage); //definindo o retorno do perPage()
        $mockPagination->shouldReceive('to')->andReturn($to); //definindo o retorno do to()
        $mockPagination->shouldReceive('from')->andReturn($from); //definindo o retorno do from()

        // criando o mock do repository
        $mockRepository = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('paginate')->andReturn($mockPagination); //definindo o retorno do paginate()

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateCategoryOutputDto::class, $responseUseCase);
        $this->assertCount(0, $responseUseCase->items);
        $this->assertSame($items, $responseUseCase->items);
        $this->assertSame($total, $responseUseCase->total);
        $this->assertSame($lastPage, $responseUseCase->last_page);
        $this->assertSame($firstPage, $responseUseCase->first_page);
        $this->assertSame($currentPage, $responseUseCase->current_page);
        $this->assertSame($perPage, $responseUseCase->per_page);
        $this->assertSame($to, $responseUseCase->to);
        $this->assertSame($from, $responseUseCase->from);

        // criando o spy do repository
        $spy = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $spy->shouldReceive('paginate')->andReturn($mockPagination); //definindo o retorno do paginate()

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($spy);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando a utilização dos métodos
        $spy->shouldHaveReceived('paginate');

        // encerrando os mocks
        Mockery::close();
    }

    public function testExecuteReturningExistingList()
    {
        // definindo os atributos a serem utilizados nos mocks da entidade
        $uuid = Uuid::uuid4()->toString();
        $name = 'name cat';
        $description = 'description cat';
        $isActive = false;
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $createdAt = $now;
        $updatedAt = $now;

        // criando o mock da entidade
        $mockEntity = Mockery::mock(Category::class, [
            $uuid,
            $name,
            $description,
            $isActive,
            $createdAt,
            $updatedAt,
        ]);

        // definindo os atributos a serem utilizados nos demais mocks
        $filter = '';
        $order = 'DESC';
        $page = 1;
        $itemsForPage = 10;
        $items = [$mockEntity, $mockEntity];
        $total = 2;
        $lastPage = 1;
        $firstPage = 1;
        $currentPage = $page;
        $perPage = $itemsForPage;
        $to = 1;
        $from = 1;

        // criando o mock do inputDto
        $mockInputDto = Mockery::mock(PaginateCategoryInputDto::class, [
            $filter,
            $order,
            $page,
            $itemsForPage,
        ]);

        // criando o mock do Pagination
        $mockPagination = Mockery::mock(stdClass::class, PaginationInterface::class);
        $mockPagination->shouldReceive('items')->andReturn($items); //definindo o retorno do items()
        $mockPagination->shouldReceive('total')->andReturn($total); //definindo o retorno do total()
        $mockPagination->shouldReceive('lastPage')->andReturn($lastPage); //definindo o retorno do lastPage()
        $mockPagination->shouldReceive('firstPage')->andReturn($firstPage); //definindo o retorno do firstPage()
        $mockPagination->shouldReceive('currentPage')->andReturn($currentPage); //definindo o retorno do currentPage()
        $mockPagination->shouldReceive('perPage')->andReturn($perPage); //definindo o retorno do perPage()
        $mockPagination->shouldReceive('to')->andReturn($to); //definindo o retorno do to()
        $mockPagination->shouldReceive('from')->andReturn($from); //definindo o retorno do from()

        // criando o mock do repository
        $mockRepository = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $mockRepository->shouldReceive('paginate')->andReturn($mockPagination); //definindo o retorno do paginate()

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($mockRepository);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando os dados
        $this->assertInstanceOf(PaginateCategoryOutputDto::class, $responseUseCase);
        $this->assertInstanceOf(Category::class, $responseUseCase->items[0]);
        $this->assertCount(2, $responseUseCase->items);
        $this->assertSame($items, $responseUseCase->items);
        $this->assertSame($total, $responseUseCase->total);
        $this->assertSame($lastPage, $responseUseCase->last_page);
        $this->assertSame($firstPage, $responseUseCase->first_page);
        $this->assertSame($currentPage, $responseUseCase->current_page);
        $this->assertSame($perPage, $responseUseCase->per_page);
        $this->assertSame($to, $responseUseCase->to);
        $this->assertSame($from, $responseUseCase->from);

        // criando o spy do repository
        $spy = Mockery::mock(stdClass::class, CategoryRepositoryInterface::class);
        $spy->shouldReceive('paginate')->andReturn($mockPagination); //definindo o retorno do paginate()

        // criando o usecase
        $useCase = new PaginateCategoryUseCase($spy);
        // executando o usecase
        $responseUseCase = $useCase->execute($mockInputDto);

        // verificando a utilização dos métodos
        $spy->shouldHaveReceived('paginate');

        // encerrando os mocks
        Mockery::close();
    }
}
