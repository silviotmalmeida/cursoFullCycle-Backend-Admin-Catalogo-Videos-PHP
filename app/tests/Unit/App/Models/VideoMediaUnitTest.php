<?php

namespace Tests\Unit\App\Models;

use App\Models\VideoMedia as VideoMediaModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// testando a model VideoMedia
// funções a serem utilizadas nos testes da ModelTestCase
class VideoMediaUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new VideoMediaModel();
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
        return 'VideoMedias';
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
            'rating' => 'string',
            'opened' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
