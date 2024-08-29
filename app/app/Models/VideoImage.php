<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models;

// importações
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// definindo a model
class VideoImage extends Model
{
    // traits a serem utilizadas
    use HasFactory;

    // definindo o nome da tabela no BD
    protected $table = 'video_images';

    // definindo os atributos a serem informados
    protected $fillable = [
        'video_id',
        'path',
        'type',
    ];

    // configurando os casts de tipagem a serem realizados
    protected $casts = [
        'video_id' => 'string',
        'path' => 'string',
        'type' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // ativando o autoincremento
    public $incrementing = true;

    // definindo o relacionamento um-para-um com video
    public function video()
    {
        return $this->belongsTo(Video::class, 'id', 'video_id');
    }
}
