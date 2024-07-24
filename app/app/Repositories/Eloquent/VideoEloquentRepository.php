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
use Core\Domain\Builder\Video\VideoBuilderInterface;
use Core\Domain\Repository\PaginationInterface;
use DateTime;

// definindo o repository, que implementa a interface VideoRepositoryInterface
class VideoEloquentRepository implements VideoRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new VideoModel(),
        protected VideoBuilderInterface $videoBuilder,
    ) {
    }

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toVideo(VideoModel $object): VideoEntity
    {
        $this->videoBuilder = new CreateVideoBuilder();

        $Video = $this->videoBuilder->createEntity($object);

        // if($object->thumbFile) $this->videoBuilder->addThumbFile($object->thumbFile);
        // if($object->thumbHalf) $this->videoBuilder->addThumbHalf($object->thumbHalf);
        // if($object->bannerFile) $this->videoBuilder->addBannerFile($object->bannerFile);
        // if($object->trailerFile) $this->videoBuilder->addTrailerFile($object->trailerFile);
        // if($object->videoFile) $this->videoBuilder->addVideoFile($object->videoFile);


        // ((bool) $object->is_active) ? $Video->activate() : $Video->deactivate();

        return $this->videoBuilder->getEntity();
    }

    // função de inserção no bd
    public function insert(VideoEntity $Video): VideoEntity
    {
        // inserindo os dados recebidos
        $response = $this->model->create(
            [
                'id' => $Video->id(),
                'name' => $Video->name,
                'is_active' => $Video->isActive,
                'created_at' => $Video->createdAt(),
                'updated_at' => $Video->updatedAt(),
            ]
        );

        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($Video->categoriesId); $i++) {
            array_push($arraySync, strval($Video->categoriesId[$i]));
        }
        $response->categories()->sync($arraySync);

        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($response);
    }

    // função de busca por id
    public function findById(string $VideoId): VideoEntity
    {
        // buscando no bd
        $VideoDb = $this->model->find($VideoId);
        // se não houver retorno, lança exceção
        if (!$VideoDb) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toVideo($VideoDb);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $genresDb = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($genresDb as $VideoDb) {
            array_push($response, $this->toVideo($VideoDb));
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
        if ($filter) $query = $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('name', $order);
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
        if ($filter) $query = $query->where('name', 'LIKE', "%{$filter}%");
        // ordenando
        $query = $query->orderBy('id', $order);
        // executando a busca paginada
        $paginator = $query->paginate($perPage);

        // organizando os dados no formato estabelecido pela interface
        return new PaginationPresenter($paginator);
    }

    // função de atualização
    public function update(VideoEntity $Video): VideoEntity
    {
        // buscando no bd
        $VideoDb = $this->model->find($Video->id());
        // se não houver retorno, lança exceção
        if (!$VideoDb) throw new NotFoundException('ID not found');
        // executando a atualização
        $VideoDb->update([
            'id' => $Video->id(),
            'name' => $Video->name,
            'is_active' => $Video->isActive,
            'updated_at' => new DateTime()
        ]);

        // sincronizando os relacionamentos
        // convertendo os valores a serem inseridos em string
        $arraySync = [];
        for ($i = 0; $i < count($Video->categoriesId); $i++) {
            array_push($arraySync, strval($Video->categoriesId[$i]));
        }
        $VideoDb->categories()->sync($arraySync);

        // forçando a atualização do registro
        $VideoDb->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toVideo($VideoDb);
    }

    // função de remoção
    public function deleteById(string $VideoId): bool
    {
        // buscando no bd
        $VideoDb = $this->model->find($VideoId);
        // se não houver retorno, lança exceção
        if (!$VideoDb) throw new NotFoundException('ID not found');
        // removendo o registro
        $VideoDb->delete();
        return true;
    }

    public function updateMedia(VideoEntity $entity): VideoEntity{

        return new VideoEntity();
    }
}
