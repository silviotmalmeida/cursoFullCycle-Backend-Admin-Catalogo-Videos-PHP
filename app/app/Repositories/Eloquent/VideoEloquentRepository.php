<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Video as VideoModel;
use App\Repositories\Presenters\PaginationPresenter;
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
        protected $model = new VideoModel()
    ) {}

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toVideo(VideoModel $model): VideoEntity
    {
        // criando a entidade
        $entity = new VideoEntity(
            id: $model->id,
            title: $model->title,
            description: $model->description,
            yearLaunched: $model->year_launched,
            duration: $model->duration,
            rating: $model->rating,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
        // atribuindo o opened
        ((bool) $model->opened) ? $entity->open() : $entity->close();
        // adicionando as categories
        if ($model->categories) {
            foreach ($model->categories as $category) {

                $entity->addCategoryId($category->id);
            }
        }
        // adicionando os genres
        if ($model->genres) {
            foreach ($model->genres as $genre) {

                $entity->addGenreId($genre->id);
            }
        }
        // adicionando os cast members
        if ($model->castMembers) {
            foreach ($model->castMembers as $castMember) {

                $entity->addCastMemberId($castMember->id);
            }
        }
        return $entity;
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
            'updated_at' => new DateTime()
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

    public function updateMedia(VideoEntity $entity): VideoEntity
    {
        return new VideoEntity();
    }
}
