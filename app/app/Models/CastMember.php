<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    // traits a serem utilizadas
    use HasFactory, SoftDeletes;

    // definindo o nome da tabela no BD
    protected $table = 'cast_members';

    // definindo os atributos a serem informados
    protected $fillable = [
        'id',
        'name',
        'type',
    ];

    // configurando os casts de tipagem a serem realizados
    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // desativando o autoincremento
    public $incrementing = false;
}
