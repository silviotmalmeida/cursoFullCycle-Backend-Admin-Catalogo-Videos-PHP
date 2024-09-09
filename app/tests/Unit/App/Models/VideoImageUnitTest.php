<?php

namespace Tests\Unit\App\Models;

use App\Models\VideoImage as VideoImageModel;
use App\Models\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// testando a model VideoImage
// funções a serem utilizadas nos testes da ModelTestCase
class VideoImageUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new VideoImageModel();
    }

    protected function requiredTraits(): array
    {
        return [
            HasFactory::class,
            UuidTrait::class,
        ];
    }

    protected function tableName(): string
    {
        return 'video_images';
    }

    protected function requiredFillable(): array
    {
        return [
            'path',
            'type',
        ];
    }

    protected function requiredCasts(): array
    {
        return [
            'video_id' => 'string',
            'path' => 'string',
            'type' => 'int',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
