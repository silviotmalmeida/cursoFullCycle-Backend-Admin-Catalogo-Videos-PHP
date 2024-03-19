<?php

namespace Tests\Feature\Core\UseCase\Genre;

use App\Models\Genre as GenreModel;
use App\Models\Category as CategoryModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Eloquent\GenreEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use Core\Domain\Exception\NotFoundException;
use Core\UseCase\Genre\InsertGenreUseCase;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreInputDto;
use Core\UseCase\DTO\Genre\InsertGenre\InsertGenreOutputDto;
use Exception;
use Tests\TestCase;

class InsertGenreUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução sem categorias
    public function testExecute()
    {
        // dados de entrada
        $name = 'name genre';
        $isActive = false;

        // criando o inputDto
        $inputDto = new InsertGenreInputDto(
            name: $name,
            isActive: $isActive
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertGenreOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'name' => $name,
            'is_active' => $isActive
        ]);
    }

    // função que testa o método de execução com categorias
    public function testExecuteWithCategories()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();

        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // dados de entrada
        $name = 'name genre';
        $isActive = false;

        // criando o inputDto
        $inputDto = new InsertGenreInputDto(
            name: $name,
            isActive: $isActive,
            categoriesId: $categoriesIds
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(InsertGenreOutputDto::class, $responseUseCase);
        $this->assertNotEmpty($responseUseCase->id);
        $this->assertSame($name, $responseUseCase->name);
        $this->assertSame($isActive, $responseUseCase->is_active);
        $this->assertCount($qtd, $responseUseCase->categories_id);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'name' => $name,
            'is_active' => $isActive
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);
    }

    // função que testa o método de execução com categorias e rollback
    public function testExecuteWithCategoriesAndRollback()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();

        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // dados de entrada
        $name = 'name genre';
        $isActive = false;

        // criando o inputDto
        $inputDto = new InsertGenreInputDto(
            name: $name,
            isActive: $isActive,
            categoriesId: $categoriesIds
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // tratamento de exceções
        try {
            // criando o usecase
            $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);
            // executando o usecase
            $useCase->execute($inputDto, true);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals($th->getMessage(), "rollback test");
            $this->assertDatabaseCount('genres', 0);
            $this->assertDatabaseCount('category_genre', 0);
        }
    }

    // função que testa o método de execução com categorias inválidas
    public function testExecuteWithInvalidCategories()
    {
        try {
            // criando o id da categoria
            $categoryId = 'fake';

            // dados de entrada
            $name = 'name genre';
            $isActive = false;
            $categoriesIds = [$categoryId];

            // criando o inputDto
            $inputDto = new InsertGenreInputDto(
                name: $name,
                isActive: $isActive,
                categoriesId: $categoriesIds
            );

            // criando o repository
            $repository = new GenreEloquentRepository(new GenreModel());

            // criando o gerenciador de transações
            $transactionDb = new TransactionDb();

            // criando o repository da Category
            $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

            // criando o usecase
            $useCase = new InsertGenreUseCase($repository, $transactionDb, $categoryRepository);

            // executando o usecase
            $useCase->execute($inputDto);

            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(NotFoundException::class, $th);
            // verificando a mensagem da exceção
            $this->assertSame($th->getMessage(), "Category $categoryId not found");
        }
    }
}
