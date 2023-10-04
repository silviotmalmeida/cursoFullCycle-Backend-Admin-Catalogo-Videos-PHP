<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações
use Core\Domain\Entity\Category;
use Core\Domain\Exception\EntityValidationException;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CategoryUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando a category
        $category = new Category(
            name: 'New Cat',
            description: 'New Desc',
            isActive: true
        );

        // verificando os atributos
        $this->assertIsString($category->id);
        $this->assertEquals('New Cat', $category->name);
        $this->assertEquals('New Desc', $category->description);
        $this->assertTrue($category->isActive);
    }

    // função que testa a função de ativação
    public function testActivate()
    {
        // criando a category
        $category = new Category(
            name: 'New Cat',
            description: 'New Desc',
            isActive: false
        );

        // ativando
        $category->activate();

        // verificando
        $this->assertTrue($category->isActive);
    }

    // função que testa a função de desativação
    public function testDeactivate()
    {
        // criando a category
        $category = new Category(
            name: 'New Cat',
            description: 'New Desc',
            isActive: true
        );

        // desativando
        $category->deactivate();

        // verificando
        $this->assertFalse($category->isActive);
    }

    // função que testa a função de atualização
    public function testUpdate()
    {
        // mock de uuid
        $uuid = 'uuid.value';

        // criando a category
        $category = new Category(
            id: $uuid,
            name: 'name 1',
            description: 'desc 1',
            isActive: true
        );

        // atualizando com valores
        $category->update(
            name: 'name 2',
            description: 'desc 2',
        );

        // verificando os atributos
        $this->assertEquals($uuid, $category->id);
        $this->assertEquals('name 2', $category->name);
        $this->assertEquals('desc 2', $category->description);
        $this->assertTrue($category->isActive);

        // atualizando sem valores
        $category->update();

        // verificando os atributos
        $this->assertEquals($uuid, $category->id);
        $this->assertEquals('name 2', $category->name);
        $this->assertEquals('desc 2', $category->description);
        $this->assertTrue($category->isActive);
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // validando name vazio
        try {
            // criando a category
            $category = new Category(
                name: '',
                description: 'desc 1',
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name longo
        try {
            // criando a category
            $category = new Category(
                name: random_bytes(256),
                description: 'desc 1',
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name curto
        try {
            // criando a category
            $category = new Category(
                name: random_bytes(2),
                description: 'desc 1',
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name válido
        try {
            // criando a category
            $category = new Category(
                name: 'name 1',
                description: 'desc 1',
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando description não vazia com tamanho longo
        try {
            // criando a category
            $category = new Category(
                name: 'name 1',
                description: random_bytes(256),
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando description não vazia com tamanho curto
        $descriptionMock = random_bytes(2);
        try {
            // criando a category
            $category = new Category(
                name: 'name 1',
                description: $descriptionMock,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando description vazia
        try {
            // criando a category
            $category = new Category(
                name: 'name 1',
                description: '',
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }
    }
}
