<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models;

// importações
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// definindo a model
class Category extends Model
{
    // traits a serem utilizadas
    use HasFactory, SoftDeletes;

    // definindo o nome da tabela no BD
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // desativando o autoincremento
    public $incrementing = false;

    // definindo o relacionamento muitos-para-muitos com genre
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'category_genre', 'category_id', 'genre_id');
    }
}
