<?php

namespace Tests\Unit\App\Models;

use App\Models\VideoMedia as VideoMediaModel;
use App\Models\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
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
            UuidTrait::class,
        ];
    }

    protected function tableName(): string
    {
        return 'video_medias';
    }

    protected function requiredFillable(): array
    {
        return [
            'file_path',
            'encoded_path',
            'status',
            'type',
        ];
    }

    protected function requiredCasts(): array
    {
        return [
            'id' => 'string',
            'video_id' => 'string',
            'file_path' => 'string',
            'encoded_path' => 'string',
            'status' => 'int',
            'type' => 'int',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
