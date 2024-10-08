<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models;

// importações
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// definindo a model
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

    // definindo o relacionamento muitos-para-muitos com video
    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_cast_member', 'cast_member_id', 'video_id');
    }
}
