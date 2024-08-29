<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Eloquent;

// importações
use App\Models\CastMember as CastMemberModel;
use App\Repositories\Presenters\PaginationPresenter;
use Core\Domain\Entity\CastMember as CastMemberEntity;
use Core\Domain\Entity\Entity;
use Core\Domain\Enum\CastMemberType;
use Core\Domain\Exception\NotFoundException;
use Core\Domain\Repository\CastMemberRepositoryInterface;
use Core\Domain\Repository\PaginationInterface;
use DateTime;

// definindo o repository, que implementa a interface CastMemberRepositoryInterface
class CastMemberEloquentRepository implements CastMemberRepositoryInterface
{
    // construtor e atributos
    public function __construct(
        protected $model = new CastMemberModel()
    ) {
    }

    // função para conversão do objeto de retorno do Eloquent para a referida entidade
    private function toCastMember(object $object): Entity
    {
        $castMember = new CastMemberEntity(
            id: $object->id,
            name: $object->name,
            type: $object->type,
            createdAt: $object->created_at,
            updatedAt: $object->updated_at
        );

        return $castMember;
    }

    // função de inserção no bd
    public function insert(Entity $castMember): Entity
    {
        // inserindo os dados recebidos
        $response = $this->model->create(
            [
                'id' => $castMember->id(),
                'name' => $castMember->name,
                'type' => $castMember->type->value,
                'created_at' => $castMember->createdAt(),
                'updated_at' => $castMember->updatedAt(),
            ]
        );

        // retornando a entidade populada com os dados inseridos
        return $this->toCastMember($response);
    }

    // função de busca por id
    public function findById(string $castMemberId): Entity
    {
        // buscando no bd
        $castMemberDb = $this->model->find($castMemberId);
        // se não houver retorno, lança exceção
        if (!$castMemberDb) throw new NotFoundException('ID not found');
        // retornando a entidade
        return $this->toCastMember($castMemberDb);
    }

    // função de busca múltipla, a partir de uma lista de id
    public function findByIdArray(array $listIds): array
    {
        // inicializando o array de saída
        $response = [];
        // buscando no bd a partir da lista recebida
        $castMembersDb = $this->model->whereIn('id', $listIds)->get();
        // convertendo os resultados para entidade
        foreach ($castMembersDb as $castMemberDb) {
            array_push($response, $this->toCastMember($castMemberDb));
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
        $query = $query->orderBy('id', $order);
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
    public function update(Entity $castMember): Entity
    {
        // buscando no bd
        $castMemberDb = $this->model->find($castMember->id());
        // se não houver retorno, lança exceção
        if (!$castMemberDb) throw new NotFoundException('ID not found');
        // executando a atualização
        $castMemberDb->update([
            'id' => $castMember->id(),
            'name' => $castMember->name,
            'type' => $castMember->type->value,
            'updated_at' => new DateTime()
        ]);
        // forçando a atualização do registro
        $castMemberDb->refresh();
        // retornando a entidade populada com os dados inseridos
        return $this->toCastMember($castMemberDb);
    }

    // função de remoção
    public function deleteById(string $castMemberId): bool
    {
        // buscando no bd
        $castMemberDb = $this->model->find($castMemberId);
        // se não houver retorno, lança exceção
        if (!$castMemberDb) throw new NotFoundException('ID not found');
        // removendo o registro
        $castMemberDb->delete();
        return true;
    }
}
