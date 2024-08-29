<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Models;

// importações

use Core\Domain\Enum\ImageType;
use Core\Domain\Enum\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// definindo a model
class Video extends Model
{
    // traits a serem utilizadas
    use HasFactory, SoftDeletes;

    // definindo o nome da tabela no BD
    protected $table = 'videos';

    // definindo os atributos a serem informados
    protected $fillable = [
        'id',
        'title',
        'description',
        'year_launched',
        'duration',
        'rating',
        'opened',
    ];

    // configurando os casts de tipagem a serem realizados
    protected $casts = [
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

    // desativando o autoincremento
    public $incrementing = false;

    // definindo o relacionamento muitos-para-muitos com category
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'video_category', 'video_id', 'category_id');
    }

    // definindo o relacionamento muitos-para-muitos com genre
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'video_genre', 'video_id', 'genre_id');
    }

    // definindo o relacionamento muitos-para-muitos com castMember
    public function castMembers()
    {
        return $this->belongsToMany(CastMember::class, 'video_cast_member', 'video_id', 'cast_member_id');
    }

    // definindo o relacionamento um-para-um com videoMedia
    public function video()
    {
        return $this->hasOne(VideoMedia::class, 'video_id', 'id')->where('type', MediaType::VIDEO->value);
    }

    // definindo o relacionamento um-para-um com videoMedia
    public function trailer()
    {
        return $this->hasOne(VideoMedia::class, 'video_id', 'id')->where('type', MediaType::TRAILER->value);
    }

    // definindo o relacionamento um-para-um com videoImage
    public function thumb()
    {
        return $this->hasOne(VideoImage::class, 'video_id', 'id')->where('type', ImageType::THUMB->value);
    }

    // definindo o relacionamento um-para-um com videoImage
    public function thumbHalf()
    {
        return $this->hasOne(VideoImage::class, 'video_id', 'id')->where('type', ImageType::THUMB_HALF->value);
    }

    // definindo o relacionamento um-para-um com videoImage
    public function banner()
    {
        return $this->hasOne(VideoImage::class, 'video_id', 'id')->where('type', ImageType::BANNER->value);
    }
}
