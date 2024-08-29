<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\Video as VideoModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\Video as VideoEntity;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\VideoRepositoryInterface;
use Core\Domain\Builder\Video\VideoBuilderInterface;
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
    private function toVideo(object $object): VideoEntity
    {

        $video = new VideoEntity(
            id: $object->id,
            title: $object->title,
            description: $object->description,
            yearLaunched: $object->year_launched,
            duration: $object->duration,
            rating: $object->rating,
            createdAt: $object->created_at,
            updatedAt: $object->updated_at
        );

        ((bool) $object->opened) ? $video->open() : $video->close();

        // adicionando as categories
        if ($object->categoriesId) {
            foreach ($object->categoriesId as $categoryId) {

                $video->addCategoryId($categoryId);
            }
        }

        // adicionando os genres
        if ($object->genresId) {
            foreach ($object->genresId as $genreId) {

                $video->addGenreId($genreId);
            }
        }

        // adicionando os cast members
        if ($object->castMembersId) {
            foreach ($object->castMembersId as $castMemberId) {

                $video->addCastMemberId($castMemberId);
            }
        }

        return $video;
    }

    // função de inserção no bd
    public function insert(VideoEntity $video): VideoEntity
    {
        // inserindo os dados recebidos
        $response = $this->model->create(
            [
                'id' => $video->id(),
                'title' => $video->title,
                'description' => $video->description,
                'year_launched' => $video->yearLaunched,
                'duration' => $video->duration,
                'rating' => $video->rating->value,
                'opened' => $video->opened,
                'created_at' => $video->createdAt(),
                'updated_at' => $video->updatedAt(),
            ]
        );
        // sincronizando os relacionamentos
        // 
        // relacionamentos com categories
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($video->categoriesId); $i++) {
            array_push($arraySync, strval($video->categoriesId[$i]));
        }
        $response->categories()->sync($arraySync);
        // 
        // relacionamentos com genres
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($video->genresId); $i++) {
            array_push($arraySync, strval($video->genresId[$i]));
        }
        $response->genres()->sync($arraySync);
        // 
        // relacionamentos com castMembers
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($video->castMembersId); $i++) {
            array_push($arraySync, strval($video->castMembersId[$i]));
        }
        $response->castMembers()->sync($arraySync);

        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($response);
    }

    // função de busca por id
    public function findById(string $videoId): VideoEntity
    {
        // buscando no bd
        $videoDb = $this->model->find($videoId);
        // se não houver retorno, lança exceção
        if (!$videoDb) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toVideo($videoDb);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $genresDb = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($genresDb as $videoDb) {
            array_push($response, $this->toVideo($videoDb));
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
        $paginator = $query->paginate($perPage);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(VideoEntity $video): VideoEntity
    {
        // buscando no bd
        $videoDb = $this->model->find($video->id());
        // se não houver retorno, lança exceção
        if (!$videoDb) throw new NotFoundException('ID not found');
        // executando a atualização
        $videoDb->update([
            'id' => $video->id(),
            'title' => $video->title,
            'is_active' => $video->isActive,
            'updated_at' => new DateTime()
        ]);

        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($video->categoriesId); $i++) {
            array_push($arraySync, strval($video->categoriesId[$i]));
        }
        $videoDb->categories()->sync($arraySync);

        // forçando a atualização do registro
        $videoDb->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($videoDb);
    }

    // função de remoção
    public function deleteById(string $videoId): bool
    {
        // buscando no bd
        $videoDb = $this->model->find($videoId);
        // se não houver retorno, lança exceção
        if (!$videoDb) throw new NotFoundException('ID not found');
        // removendo o registro
        $videoDb->delete();
        return true;
    }

    public function updateMedia(VideoEntity $entity): VideoEntity
    {

        return new VideoEntity();
    }
}
