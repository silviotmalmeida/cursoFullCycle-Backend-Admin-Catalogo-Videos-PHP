<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests;

// importações

use Core\Domain\Entity\CastMember;
use Core\Domain\Entity\Category;
use Core\Domain\Entity\Genre;
use Core\Domain\Enum\CastMemberType;
use DateTime;
use Mockery;

// definindo a classe
abstract class MocksFactory
{
    // método auxiliar para criar um mock da entidade category
    static function createCategoryMock(string $id, string $name, string $description, bool $isActive): Category
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $mock = Mockery::mock(Category::class, [
            $id,
            $name,
            $description,
            $isActive,
        ]);
        $mock->shouldReceive('id')->andReturn($id); //definindo o retorno do id()
        $mock->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mock->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        return $mock;
    }

    // método auxiliar para criar um mock da entidade genre
    static function createGenreMock(string $id, string $name, bool $isActive, array $categoriesId): Genre
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $mock = Mockery::mock(Genre::class, [
            $id,
            $name,
            $isActive,
            $categoriesId
        ]);
        $mock->shouldReceive('id')->andReturn($id); //definindo o retorno do id()
        $mock->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mock->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        return $mock;
    }

    // método auxiliar para criar um mock da entidade cast member
    static function createCastMemberMock(string $id, string $name, CastMemberType $type): CastMember
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $mock = Mockery::mock(CastMember::class, [
            $id,
            $name,
            $type,
        ]);
        $mock->shouldReceive('id')->andReturn($id); //definindo o retorno do id()
        $mock->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mock->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        return $mock;
    }
}
