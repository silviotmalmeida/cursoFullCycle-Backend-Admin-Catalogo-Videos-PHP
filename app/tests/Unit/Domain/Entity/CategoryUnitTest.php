<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações

use Core\Domain\Entity\Category;
use PHPUnit\Framework\TestCase;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CategoryUnitTest extends TestCase
{
    // função que testa os atributos da criação
    public function testAttributes()
    {
        // criando a category
        $category = new Category(
            id: '123',
            name: 'New Cat',
            description: 'New Desc',
            isActive: true
        );

        // verificando os atributos
        $this->assertEquals('New Cat', $category->name);
        $this->assertEquals('New Desc', $category->description);
        $this->assertTrue($category->isActive);
    }
}
