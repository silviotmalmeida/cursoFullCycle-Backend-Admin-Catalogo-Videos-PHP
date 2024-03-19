<?php

namespace Tests\Feature\Core\UseCase\Genre;

use App\Models\Category as CategoryModel;
use App\Models\Genre as GenreModel;
use App\Repositories\Eloquent\CategoryEloquentRepository;
use App\Repositories\Eloquent\GenreEloquentRepository;
use App\Repositories\Transactions\TransactionDb;
use Core\UseCase\Genre\UpdateGenreUseCase;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreInputDto;
use Core\UseCase\DTO\Genre\UpdateGenre\UpdateGenreOutputDto;
use Exception;
use Tests\TestCase;

class UpdateGenreUseCaseFeatureTest extends TestCase
{
    // função que testa o método de execução
    public function testExecute()
    {
        // inserindo um registro no bd
        $model = GenreModel::factory()->create();
        sleep(1);

        // alterando o valor do isActive
        $isActiveAlternate = ($model->is_active) ? false : true;

        // criando o inputDto
        $inputDto =  new UpdateGenreInputDto(
            id: $model->id,
            name: "updated name",
            isActive: $isActiveAlternate,
            categoriesId: [],
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotSame($model->name, $responseUseCase->name);
        $this->assertSame($inputDto->name, $responseUseCase->name);
        $this->assertSame($inputDto->isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'id' => $model->id,
            'name' => $inputDto->name,
            'is_active' => $inputDto->isActive
        ]);

        $this->assertDatabaseCount('category_genre', 0);
    }

    // função que testa o método de execução com categorias
    public function testExecuteWithCategories()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();

        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // inserindo um registro no bd
        $model = GenreModel::factory()->create();
        sleep(1);

        // alterando o valor do isActive
        $isActiveAlternate = ($model->is_active) ? false : true;

        // criando o inputDto
        $inputDto =  new UpdateGenreInputDto(
            id: $model->id,
            name: "updated name",
            isActive: $isActiveAlternate,
            categoriesId: $categoriesIds,
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotSame($model->name, $responseUseCase->name);
        $this->assertSame($inputDto->name, $responseUseCase->name);
        $this->assertSame($inputDto->isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'id' => $model->id,
            'name' => $inputDto->name,
            'is_active' => $inputDto->isActive
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);

        // executando outra atualização no mesmo registro
        // alterando o valor do isActive
        $isActiveAlternate = ($model->is_active) ? false : true;

        // criando o inputDto
        $inputDto =  new UpdateGenreInputDto(
            id: $model->id,
            name: "double updated name",
            isActive: $isActiveAlternate,
            categoriesId: [$categoriesIds[0]],
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto);

        // verificando os dados
        $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotSame($model->name, $responseUseCase->name);
        $this->assertSame($inputDto->name, $responseUseCase->name);
        $this->assertSame($inputDto->isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'id' => $model->id,
            'name' => $inputDto->name,
            'is_active' => $inputDto->isActive
        ]);

        $this->assertDatabaseCount('category_genre', 1);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesIds[0],
            'genre_id' => $model->id
        ]);
    }

    // função que testa o método de execução com categorias e rollback
    public function testExecuteWithCategoriesAndRollback()
    {
        // criando as categorias
        $qtd = random_int(10, 20);
        $categories = CategoryModel::factory()->count($qtd)->create();

        // obtendo o array de id das categorias
        $categoriesIds = $categories->pluck('id')->toArray();

        // inserindo um registro no bd
        $model = GenreModel::factory()->create();
        sleep(1);

        // alterando o valor do isActive
        $isActiveAlternate = ($model->is_active) ? false : true;

        // criando o inputDto
        $inputDto1 =  new UpdateGenreInputDto(
            id: $model->id,
            name: "updated name",
            isActive: $isActiveAlternate,
            categoriesId: $categoriesIds,
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateGenreUseCase($repository, $transactionDb, $categoryRepository);

        // executando o usecase
        $responseUseCase = $useCase->execute($inputDto1);

        // verificando os dados
        $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
        $this->assertSame($model->id, $responseUseCase->id);
        $this->assertNotSame($model->name, $responseUseCase->name);
        $this->assertSame($inputDto1->name, $responseUseCase->name);
        $this->assertSame($inputDto1->isActive, $responseUseCase->is_active);
        $this->assertNotEmpty($responseUseCase->created_at);
        $this->assertNotEmpty($responseUseCase->updated_at);
        $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

        $this->assertDatabaseHas('genres', [
            'id' => $model->id,
            'name' => $inputDto1->name,
            'is_active' => $inputDto1->isActive
        ]);

        $this->assertDatabaseCount('category_genre', $qtd);

        // executando outra atualização no mesmo registro, agora com rollback
        // alterando o valor do isActive
        $isActiveAlternate = ($model->is_active) ? false : true;

        // criando o inputDto
        $inputDto2 =  new UpdateGenreInputDto(
            id: $model->id,
            name: "double updated name",
            isActive: $isActiveAlternate,
            categoriesId: [$categoriesIds[0]],
        );

        // criando o repository
        $repository = new GenreEloquentRepository(new GenreModel());

        // criando o gerenciador de transações
        $transactionDb = new TransactionDb();

        // criando o repository da Category
        $categoryRepository = new CategoryEloquentRepository(new CategoryModel());

        // criando o usecase
        $useCase = new UpdateGenreUseCase($repository, $transactionDb, $categoryRepository);

        // tratamento de exceções
        try {
            // executando o usecase
            $useCase->execute($inputDto2, true);
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals($th->getMessage(), "rollback test");
            // verificando os dados
            $this->assertInstanceOf(UpdateGenreOutputDto::class, $responseUseCase);
            $this->assertSame($model->id, $responseUseCase->id);
            $this->assertNotSame($model->name, $responseUseCase->name);
            $this->assertSame($inputDto1->name, $responseUseCase->name);
            $this->assertSame($inputDto1->isActive, $responseUseCase->is_active);
            $this->assertNotEmpty($responseUseCase->created_at);
            $this->assertNotEmpty($responseUseCase->updated_at);
            $this->assertNotSame($responseUseCase->created_at, $responseUseCase->updated_at);

            $this->assertDatabaseHas('genres', [
                'id' => $model->id,
                'name' => $inputDto1->name,
                'is_active' => $inputDto1->isActive
            ]);

            $this->assertDatabaseCount('category_genre', $qtd);
        }
    }
}
