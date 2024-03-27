<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Unit\Domain\Entity;

// importações
use Core\Domain\Entity\CastMember;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Exception\EntityValidationException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;

// definindo a classe de teste, que estende a TestCase do PHPUnit
class CastMemberUnitTest extends TestCase
{
    // função que testa o construtor
    public function testConstructor()
    {
        // criando o castMember
        $castMember = new CastMember(
            name: 'New CastMember',
            type: CastMemberType::ACTOR
        );

        // verificando os atributos
        $this->assertNotEmpty($castMember->id());
        $this->assertSame('New CastMember', $castMember->name);
        $this->assertSame(CastMemberType::ACTOR, $castMember->type);
        $this->assertNotEmpty($castMember->createdAt());
        $this->assertNotEmpty($castMember->updatedAt());
        $this->assertSame($castMember->createdAt(), $castMember->updatedAt());
    }

    // função que testa a função de atualização
    public function testUpdate()
    {
        // mock de uuid
        $uuid = RamseyUuid::uuid4()->toString();
        $cat1 = RamseyUuid::uuid4()->toString();
        $cat2 = RamseyUuid::uuid4()->toString();
        $cat3 = RamseyUuid::uuid4()->toString();

        // criando o castMember
        $castMember = new CastMember(
            id: $uuid,
            name: 'name 1',
            type: CastMemberType::DIRECTOR,
        );

        // retardo na execução para permitir diferenciação do updatedAt
        sleep(1);

        // atualizando com valores
        $castMember->update(
            name: 'name 2',
            type: CastMemberType::ACTOR,
        );

        // memorizando a data da primeira atualização para comparar com a segunda
        $firstUpdateDate = $castMember->updatedAt();

        // verificando os atributos
        $this->assertSame($uuid, $castMember->id());
        $this->assertSame('name 2', $castMember->name);
        $this->assertSame(CastMemberType::ACTOR, $castMember->type);
        $this->assertNotSame($castMember->createdAt(), $castMember->updatedAt());

        // atualizando sem valores, o updatedAt não deve ser modificado
        $castMember->update();

        // verificando os atributos
        $this->assertSame($uuid, $castMember->id());
        $this->assertSame('name 2', $castMember->name);
        $this->assertSame(CastMemberType::ACTOR, $castMember->type);
        $this->assertSame($firstUpdateDate, $castMember->updatedAt());
    }

    // função que testa a função de validação
    public function testValidate()
    {
        // validando name vazio
        try {
            // criando o castMember
            $castMember = new CastMember(
                name: '',
                type: CastMemberType::ACTOR,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name longo
        try {
            // criando o castMember
            $castMember = new CastMember(
                name: random_bytes(256),
                type: CastMemberType::ACTOR,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name curto
        try {
            // criando o castMember
            $castMember = new CastMember(
                name: random_bytes(2),
                type: CastMemberType::ACTOR,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }

        // validando name válido
        try {
            // criando o castMember
            $castMember = new CastMember(
                name: 'name 1',
                type: CastMemberType::ACTOR,
            );
        } catch (\Throwable $th) {
            // se lançar exceção o teste deve falhar
            $this->assertTrue(false);
        }

        // validando type nulo
        try {
            // criando o castMember
            $castMember = new CastMember(
                name: 'name 1',
                type: null,
            );
            // se não lançar exceção o teste deve falhar
            $this->assertTrue(false);
        } catch (\Throwable $th) {
            // verificando o tipo da exceção            
            $this->assertInstanceOf(EntityValidationException::class, $th);
        }
    }
}
