<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Uuid;
use DateTime;

// definindo a entidade
class Genre extends Entity
{
    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $name = '',
        protected bool $isActive = true,
        protected array $categoriesId = [],
        protected DateTime|string $createdAt = '',
        protected DateTime|string $updatedAt = '',
    ) {
        // incluindo as regras do médoto de criação da classe-mãe
        parent::__construct();

        // processamento do id
        // se o id for vazio, atribui um uuid randomicamente
        if ($this->id == '') {
            $this->id = Uuid::random();
        }
        // senão, converte a string recebida para um objeto de valor uuid
        else {
            $this->id = new Uuid($this->id);
        }

        // processamento do createdAt
        // se o createdAt for vazio, atribui a data atual
        if ($this->createdAt == '') {
            $this->createdAt = new DateTime();
        }
        // senão, converte a string recebida para um Datetime
        else {
            $this->createdAt = new DateTime($this->createdAt);
        }

        // processamento do updatedAt
        // se o updatedAt for vazio, atribui a data atual
        if ($this->updatedAt == '') {
            $this->updatedAt = new DateTime();
        }
        // senão, converte a string recebida para um Datetime
        else {
            $this->updatedAt = new DateTime($this->updatedAt);
        }

        // validando os atributos
        $this->validate();
    }

    // função de ativação
    public function activate(): void
    {
        $this->isActive = true;
    }

    // função de desativação
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    // função de atribuição de category
    public function addCategory(Uuid|string $categoryId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($categoryId instanceof Uuid)) $categoryId = new Uuid($categoryId);
        // adicionando no array de categories
        if (!(in_array($categoryId, $this->categoriesId))) array_push($this->categoriesId, $categoryId);
        // removendo duplicatas
        $this->categoriesId = array_unique($this->categoriesId);
    }

    // função de remoção de category
    public function removeCategory(Uuid|string $categoryId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($categoryId instanceof Uuid)) $categoryId = new Uuid($categoryId);
        // removendo do array de categories
        if (in_array($categoryId, $this->categoriesId)) $this->categoriesId = array_diff($this->categoriesId, [$categoryId]);
        // removendo duplicatas
        $this->categoriesId = array_unique($this->categoriesId);
    }

    // função de atualização dos atributos possíveis
    public function update(
        ?string $name = null,
        ?bool $isActive = null,
        ?array $categoriesId = null
    ): void {
        // atualiza somente os atributos com valores recebidos
        if (isset($name)) $this->name = $name;
        if (isset($isActive)) {
            if ($isActive === true) {
                $this->activate();
            } else if ($isActive === false) {
                $this->deactivate();
            }
        }
        if (isset($categoriesId)) {
            // removendo as categorias previamente cadastradas
            foreach ($this->categoriesId as $category) {
                $this->removeCategory($category);
            }
            // inserindo as novas categorias
            foreach ($categoriesId as $category) {
                $this->addCategory($category);
            }
        }

        // atualiza o updatedAt com a data atual
        if (isset($name) or isset($isActive) or isset($categoriesId)) $this->updatedAt = new DateTime();

        // validando os atributos
        $this->validate();
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do name
        DomainValidation::notNullOrEmpty($this->name);
        DomainValidation::strMaxLenght($this->name);
        DomainValidation::strMinLenght($this->name);
    }
}
