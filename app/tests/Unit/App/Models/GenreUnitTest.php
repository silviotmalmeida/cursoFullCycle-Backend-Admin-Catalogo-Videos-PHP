<?php

namespace Tests\Unit\App\Models;

use App\Models\Genre as GenreModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// testando a model Genre
// funções a serem utilizadas nos testes da ModelTestCase
class GenreUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new GenreModel();
    }

    protected function requiredTraits(): array
    {
        return [
            HasFactory::class,
            SoftDeletes::class
        ];
    }

    protected function tableName(): string
    {
        return 'genres';
    }

    protected function requiredFillable(): array
    {
        return [
            'id',
            'name',
            'is_active',
        ];
    }

    protected function requiredCasts(): array
    {
        return [
            'id' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
