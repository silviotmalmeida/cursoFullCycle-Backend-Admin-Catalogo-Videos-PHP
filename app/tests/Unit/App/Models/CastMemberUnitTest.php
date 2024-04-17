<?php

namespace Tests\Unit\App\Models;

use App\Models\CastMember as CastMemberModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// testando a model CastMember
// funções a serem utilizadas nos testes da ModelTestCase
class CastMemberUnitTest extends ModelTestCase
{
    protected function model(): Model
    {
        return new CastMemberModel();
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
        return 'cast_members';
    }

    protected function requiredFillable(): array
    {
        return [
            'id',
            'name',
            'type',
        ];
    }

    protected function requiredCasts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }
}
