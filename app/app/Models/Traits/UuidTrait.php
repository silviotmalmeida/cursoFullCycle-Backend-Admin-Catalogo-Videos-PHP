<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models\Traits;

// importações
use Exception;
use Illuminate\Support\Str;

// definindo a trait que vai possibilitar a criação do uuid para o id de VideoMedia e VideoImage
trait UuidTrait
{

    // sobrescrevendo o método booted para permitir a geração de uuid ao criar a model
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
