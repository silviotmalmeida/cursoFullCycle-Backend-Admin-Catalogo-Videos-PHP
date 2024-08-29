<?php

namespace Tests\Unit\App\Models;

use App\Models\Video as VideoModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// testando a model Video
// funções a serem utilizadas nos testes da ModelTestCase
class VideoUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new VideoModel();
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
        return 'videos';
    }

    protected function requiredFillable(): array
    {
        return [
            'id',
            'title',
            'description',
            'year_launched',
            'duration',
            'rating',
            'opened',
        ];
    }

    protected function requiredCasts(): array
    {
        return [
            'id' => 'string',
            'title' => 'string',
            'description' => 'string',
            'year_launched' => 'int',
            'duration' => 'int',
            'rating' => 'int',
            'opened' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
