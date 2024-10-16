<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Video as VideoModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Builder\Video\CreateVideoBuilder;
use Core\Domain\Entity\Video as VideoEntity;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\Domain\Repository\PaginationInterface;
use DateTime;

// definindo o repository, que implementa a interface VideoRepositoryInterface
class VideoEloquentRepository implements VideoRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new VideoModel(),
        protected $videoBuilder = new CreateVideoBuilder()
    ) {}

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toVideo(VideoModel $model): VideoEntity
    {
        // obtendo o array de id das categorias, genres e castMembers
        $categoriesIds = $model->categories->pluck('id')->toArray();
        $genresIds = $model->genres->pluck('id')->toArray();
        $castMembersIds = $model->castMembers->pluck('id')->toArray();

        $this->videoBuilder->createEntity(
            (object) array(
                'id' => $model->id,
                'title' => $model->title,
                'description' => $model->description,
                'yearLaunched' => $model->year_launched,
                'duration' => $model->duration,
                'rating' => $model->rating,
                'createdAt' => $model->created_at,
                'updatedAt' => $model->updated_at,
                'opened' => $model->opened,
                'categoriesId' => $categoriesIds,
                'genresId' => $genresIds,
                'castMembersId' => $castMembersIds,
            )
        );

        // adicionando o trailer
        if ($trailer = $model->trailer()->first()) {
            $this->videoBuilder->addTrailerFile($trailer->file_path, $trailer->status);
        }
        // adicionando o videoMedia
        if ($videoMedia = $model->video()->first()) {
            $this->videoBuilder->addVideoFile($videoMedia->file_path, $videoMedia->status);
        }
        // adicionando o thumb
        if ($thumb = $model->thumb()->first()) {
            $this->videoBuilder->addThumbFile($thumb->path);
        }
        // adicionando o thumbHalf
        if ($thumbHalf = $model->thumbHalf()->first()) {
            $this->videoBuilder->addThumbHalf($thumbHalf->path);
        }
        // adicionando o banner
        if ($banner = $model->banner()->first()) {
            $this->videoBuilder->addBannerFile($banner->path);
        }
        return $this->videoBuilder->getEntity();
    }

    // função auxiliar para sincronizar os relacionamentos
    private function syncRelationships(VideoEntity $entity, VideoModel $model)
    {
        // sincronizando os relacionamentos
        // 
        // relacionamentos com categories
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($entity->categoriesId); $i++) {
            array_push($arraySync, strval($entity->categoriesId[$i]));
        }
        $model->categories()->sync($arraySync);
        // 
        // relacionamentos com genres
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($entity->genresId); $i++) {
            array_push($arraySync, strval($entity->genresId[$i]));
        }
        $model->genres()->sync($arraySync);
        // 
        // relacionamentos com castMembers
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($entity->castMembersId); $i++) {
            array_push($arraySync, strval($entity->castMembersId[$i]));
        }
        $model->castMembers()->sync($arraySync);
    }

    // função de inserção no bd
    public function insert(VideoEntity $entity): VideoEntity
    {
        // inserindo os dados recebidos
        $model = $this->model->create(
            [
                'id' => $entity->id(),
                'title' => $entity->title,
                'description' => $entity->description,
                'year_launched' => $entity->yearLaunched,
                'duration' => $entity->duration,
                'rating' => $entity->rating->value,
                'opened' => $entity->opened,
                'created_at' => $entity->createdAt(),
                'updated_at' => $entity->updatedAt(),
            ]
        );
        // sincronizando os relacionamentos
        $this->syncRelationships($entity, $model);

        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($model);
    }

    // função de busca por id
    public function findById(string $videoId): VideoEntity
    {
        // buscando no bd
        $model = $this->model->find($videoId);
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toVideo($model);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $models = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($models as $model) {
            array_push($response, $this->toVideo($model));
        }
        // retornando a lista de entidades
        return $response;
    }

    // função de busca geral
    public function findAll(string $filter = '', string $order = 'DESC'): array
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query = $query->where('title', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('title', $order);
        // executando a busca
        $response = $query->get();
        // retornando os dados
        return $response->toArray();
    }

    // função de busca paginada
    public function paginate(string $filter = '', string $order = 'DESC', int $page = 1, int $perPage = 15): PaginationInterface
    {
        // iniciando a busca
        $query = $this->model;
        // aplicando o filtro, se existir
        if ($filter) $query = $query->where('title', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('id', $order);
        // executando a busca paginada
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(VideoEntity $entity): VideoEntity
    {
        // buscando no bd
        $model = $this->model->find($entity->id());
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // executando a atualização
        $model->update([
            'id' => $entity->id(),
            'title' => $entity->title,
            'description' => $entity->description,
            'year_launched' => $entity->yearLaunched,
            'duration' => $entity->duration,
            'rating' => $entity->rating->value,
            'opened' => $entity->opened,
            'updated_at' => $entity->updatedAt()
        ]);
        // sincronizando os relacionamentos
        $this->syncRelationships($entity, $model);

        // forçando a atualização do registro
        $model->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($model);
    }

    // função de remoção
    public function deleteById(string $videoId): bool
    {
        // buscando no bd
        $model = $this->model->find($videoId);
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');
        // removendo o registro
        $model->delete();
        return true;
    }

    // função de atualização das medias
    public function updateMedia(VideoEntity $entity): VideoEntity
    {
        // buscando no bd
        $model = $this->model->find($entity->id());
        // se não houver retorno, lança exceção
        if (!$model) throw new NotFoundException('ID not found');

        // atualizando o trailer
        // obtendo o trailer
        $trailer = $entity->trailerFile();
        // obtendo o registro no bd
        $trailerBD = $model->trailer()->first();
        // se estiver setado,
        if ($trailer) {
            // se já existir registro no bd, atualiza o registro
            if ($trailerBD) {
                // atualizando o registro
                $model->trailer()->update([
                    'file_path' => $trailer->filePath(),
                    'encoded_path' => $trailer->encodedPath(),
                    'status' => $trailer->mediaStatus()->value,
                    'type' => $trailer->mediaType()->value,
                ]);
            }
            // se não existir registro no BD, cria o registro
            else {
                // criando o registro
                $model->trailer()->create([
                    'file_path' => $trailer->filePath(),
                    'encoded_path' => $trailer->encodedPath(),
                    'status' => $trailer->mediaStatus()->value,
                    'type' => $trailer->mediaType()->value,
                ]);
            }
        }
        // se não estiver setado
        else {
            // se já existir registro no bd, apaga o registro
            if ($trailerBD) {
                // apagando o registro
                $trailerBD->delete();
            }
        }

        // atualizando o video
        // obtendo o video
        $video = $entity->videoFile();
        // obtendo o registro no bd
        $videoBD = $model->video()->first();
        // se estiver setado,
        if ($video) {
            // se já existir registro no bd, atualiza o registro
            if ($videoBD) {
                // atualizando o registro
                $model->video()->update([
                    'file_path' => $video->filePath(),
                    'encoded_path' => $video->encodedPath(),
                    'status' => $video->mediaStatus()->value,
                    'type' => $video->mediaType()->value,
                ]);
            }
            // se não existir registro no BD, cria o registro
            else {
                // criando o registro
                $model->video()->create([
                    'file_path' => $video->filePath(),
                    'encoded_path' => $video->encodedPath(),
                    'status' => $video->mediaStatus()->value,
                    'type' => $video->mediaType()->value,
                ]);
            }
        }
        // se não estiver setado
        else {
            // se já existir registro no bd, apaga o registro
            if ($videoBD) {
                // apagando o registro
                $videoBD->delete();
            }
        }

        // atualizando o thumbFile
        // obtendo o thumbFile
        $thumbFile = $entity->thumbFile();
        // obtendo o registro no bd
        $thumbFileBD = $model->thumb()->first();
        // se estiver setado,
        if ($thumbFile) {
            // se já existir registro no bd, atualiza o registro
            if ($thumbFileBD) {
                // atualizando o registro
                $model->thumb()->update([
                    'path' => $thumbFile->filePath(),
                    'type' => $thumbFile->imageType()->value,
                ]);
            }
            // se não existir registro no BD, cria o registro
            else {
                // criando o registro
                $model->thumb()->create([
                    'path' => $thumbFile->filePath(),
                    'type' => $thumbFile->imageType()->value,
                ]);
            }
        }
        // se não estiver setado
        else {
            // se já existir registro no bd, apaga o registro
            if ($thumbFileBD) {
                // apagando o registro
                $thumbFileBD->delete();
            }
        }

        // atualizando o thumbHalf
        // obtendo o thumbHalf
        $thumbHalf = $entity->thumbHalf();
        // obtendo o registro no bd
        $thumbHalfBD = $model->thumbHalf()->first();
        // se estiver setado,
        if ($thumbHalf) {
            // se já existir registro no bd, atualiza o registro
            if ($thumbHalfBD) {
                // atualizando o registro
                $model->thumbHalf()->update([
                    'path' => $thumbHalf->filePath(),
                    'type' => $thumbHalf->imageType()->value,
                ]);
            }
            // se não existir registro no BD, cria o registro
            else {
                // criando o registro
                $model->thumbHalf()->create([
                    'path' => $thumbHalf->filePath(),
                    'type' => $thumbHalf->imageType()->value,
                ]);
            }
        }
        // se não estiver setado
        else {
            // se já existir registro no bd, apaga o registro
            if ($thumbHalfBD) {
                // apagando o registro
                $thumbHalfBD->delete();
            }
        }

        // atualizando o bannerFile
        // obtendo o bannerFile
        $bannerFile = $entity->bannerFile();
        // obtendo o registro no bd
        $bannerFileBD = $model->banner()->first();
        // se estiver setado,
        if ($bannerFile) {
            // se já existir registro no bd, atualiza o registro
            if ($bannerFileBD) {
                // atualizando o registro
                $model->banner()->update([
                    'path' => $bannerFile->filePath(),
                    'type' => $bannerFile->imageType()->value,
                ]);
            }
            // se não existir registro no BD, cria o registro
            else {
                // criando o registro
                $model->banner()->create([
                    'path' => $bannerFile->filePath(),
                    'type' => $bannerFile->imageType()->value,
                ]);
            }
        }
        // se não estiver setado
        else {
            // se já existir registro no bd, apaga o registro
            if ($bannerFileBD) {
                // apagando o registro
                $bannerFileBD->delete();
            }
        }

        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($model);
    }
}
