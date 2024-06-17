<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests;

// importações

use Core\Domain\Entity\CastMember;
use Core\Domain\Entity\Category;
use Core\Domain\Entity\Genre;
use Core\Domain\Entity\Video;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Enum\Rating;
use Core\UseCase\Video\Insert\DTO\InsertVideoInputDto;
use DateTime;
use Mockery;
use Mockery\MockInterface;

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

    // método auxiliar para criar um mock da entidade video
    static function createVideoMock(string $id, string $title, string $description, int $yearLaunched, int $duration, bool $opened, Rating $rating, array $categoriesId, array $genresId, array $castMembersId): Video
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $mock = Mockery::mock(Video::class, [
            $id, $title, $description, $yearLaunched, $duration, $opened, $rating, $categoriesId, $genresId, $castMembersId
        ]);

        $mock->shouldReceive('id')->andReturn($id); //definindo o retorno do id()
        $mock->shouldReceive('thumbFile')->andReturn(null); //definindo o retorno do thumbFile()
        $mock->shouldReceive('thumbHalf')->andReturn(null); //definindo o retorno do thumbHalf()
        $mock->shouldReceive('bannerFile')->andReturn(null); //definindo o retorno do bannerFile()
        $mock->shouldReceive('trailerFile')->andReturn(null); //definindo o retorno do trailerFile()
        $mock->shouldReceive('videoFile')->andReturn(null); //definindo o retorno do videoFile()
        $mock->shouldReceive('open'); //definindo o retorno do open()
        $mock->shouldReceive('close'); //definindo o retorno do close()
        $mock->shouldReceive('createdAt')->andReturn($now); //definindo o retorno do createdAt()
        $mock->shouldReceive('updatedAt')->andReturn($now); //definindo o retorno do updatedAt()

        return $mock;
    }


    // método auxiliar para criar um mock do InsertVideoInputDto
    static function creatInsertVideoInputDtoMock(Video $entity): InsertVideoInputDto
    {
        $mock = Mockery::mock(InsertVideoInputDto::class, [
            $entity->title,
            $entity->description,
            $entity->yearLaunched,
            $entity->duration,
            $entity->opened,
            $entity->rating,
            $entity->categoriesId,
            $entity->genresId,
            $entity->castMembersId,
            null,
            null,
            null,
            null,
            null,
        ]);

        return $mock;
    }
}
