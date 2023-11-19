<?php

namespace Tests\Unit\App\Models;

use App\Models\Category as CategoryModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new CategoryModel();
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
        return 'categories';
    }

    protected function requiredFillable(): array
    {
        return [
            'id',
            'name',
            'description',
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
