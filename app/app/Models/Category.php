<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models;

// importações
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// definindo a model
class Category extends Model
{
    use HasFactory;

    ///definindo o nome da tabela no BD
    protected $table = 'categories';

    // definindo os atributos a serem informados
    protected $fillable = [
        'id',
        'name',
        'description',
        'is_active',
    ];

    // configurando os casts de tipagem a serem realizados
    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
    ];

    // desativando o autoincremento
    public $incrementing = false;
}
