<?php

namespace Tests\Unit\App\Models;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

// classe abstrata que deve ser extendida pelas classes que testam as models
abstract class ModelTestCase extends TestCase
{
    // métodos abstratos, que devem ser implementados pelas classes filhas
    abstract protected function model(): Model;
    abstract protected function requiredTraits(): array;
    abstract protected function tableName(): string;
    abstract protected function requiredFillable(): array;
    abstract protected function requiredCasts(): array;

    // testando as traits requeridas
    public function testRequiredTraits()
    {
        // traits requeridas
        $requiredTraits = $this->requiredTraits();

        // traits utilizadas
        $usedTraits = array_keys(class_uses($this->model()));

        $this->assertSame($requiredTraits, $usedTraits);
    }

    // testando o nome da tabela
    public function testTableName()
    {
        $this->assertSame($this->tableName(), $this->model()->getTable());
    }

    // testando a fillable requerida
    public function testRequiredFillable()
    {
        // fillable requerida
        $requiredFillable = $this->requiredFillable();

        // fillable utilizada
        $usedFillable = $this->model()->getFillable();

        $this->assertSame($requiredFillable, $usedFillable);
    }

    // testando as casts requeridas
    public function testRequiredCasts()
    {
        // casts requeridas
        $requiredCasts = $this->requiredCasts();

        // casts utilizadas
        $usedCasts = $this->model()->getCasts();

        $this->assertSame($requiredCasts, $usedCasts);
    }

    // testando se o atributo $incrementing está false
    public function testIncrementingIsFalse()
    {
        $this->assertFalse($this->model()->incrementing);
    }
}
