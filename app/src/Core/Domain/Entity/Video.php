<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Enum\Rating;
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Uuid;
use DateTime;

// definindo a entidade
class Video
{
    // incluindo a trait que ativa os métodos mágicos
    use MagicMethodsTrait;

    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $title = '',
        protected string $description = '',
        protected int $yearLaunched = 0,
        protected int $duration = 0,
        protected bool $opened = false,
        protected array $categoriesId = [],
        protected array $genresId = [],
        protected array $castMembersId = [],
        protected Rating|string $rating = '',
        protected DateTime|string $createdAt = '',
        protected DateTime|string $updatedAt = '',
    ) {
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

    // função de abertura
    public function open(): void
    {
        $this->opened = true;
    }

    // função de fechamento
    public function close(): void
    {
        $this->opened = false;
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

    // função de atribuição de genre
    public function addGenre(Uuid|string $genreId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($genreId instanceof Uuid)) $genreId = new Uuid($genreId);
        // adicionando no array de genres
        if (!(in_array($genreId, $this->genresId))) array_push($this->genresId, $genreId);
        // removendo duplicatas
        $this->genresId = array_unique($this->genresId);
    }

    // função de remoção de genre
    public function removeGenre(Uuid|string $genreId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($genreId instanceof Uuid)) $genreId = new Uuid($genreId);
        // removendo do array de genres
        if (in_array($genreId, $this->genresId)) $this->genresId = array_diff($this->genresId, [$genreId]);
        // removendo duplicatas
        $this->genresId = array_unique($this->genresId);
    }

    // função de atribuição de cast member
    public function addCastMember(Uuid|string $castMemberId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($castMemberId instanceof Uuid)) $castMemberId = new Uuid($castMemberId);
        // adicionando no array de cast members
        if (!(in_array($castMemberId, $this->castMembersId))) array_push($this->castMembersId, $castMemberId);
        // removendo duplicatas
        $this->castMembersId = array_unique($this->castMembersId);
    }

    // função de remoção de cast member
    public function removeCastMember(Uuid|string $castMemberId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($castMemberId instanceof Uuid)) $castMemberId = new Uuid($castMemberId);
        // removendo do array de cast members
        if (in_array($castMemberId, $this->castMembersId)) $this->castMembersId = array_diff($this->castMembersId, [$castMemberId]);
        // removendo duplicatas
        $this->castMembersId = array_unique($this->castMembersId);
    }

    // função de atualização dos atributos possíveis
    public function update(
        ?string $title = null,
        ?string $description = null,
        ?int $yearLaunched = null,
        ?int $duration = null,
        ?bool $opened = null,
        ?Rating $rating = null,
        ?array $categoriesId = null,
        ?array $genresId = null,
        ?array $castMembersId = null
    ): void {
        // atualiza somente os atributos com valores recebidos
        if (isset($title)) $this->title = $title;
        if (isset($description)) $this->description = $description;
        if (isset($yearLaunched)) $this->yearLaunched = $yearLaunched;
        if (isset($duration)) $this->duration = $duration;

        if (isset($opened)) {
            if ($opened === true) {
                $this->open();
            } else if ($opened === false) {
                $this->close();
            }
        }

        if (isset($rating)) $this->rating = $rating;

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

        if (isset($genresId)) {
            // removendo os genres previamente cadastrados
            foreach ($this->genresId as $genre) {
                $this->removeCategory($genre);
            }
            // inserindo os novos genres
            foreach ($genresId as $genre) {
                $this->addCategory($genre);
            }
        }

        if (isset($castMembersId)) {
            // removendo os cast members previamente cadastrados
            foreach ($this->castMembersId as $castMember) {
                $this->removeCategory($castMember);
            }
            // inserindo os novos cast members
            foreach ($castMembersId as $castMember) {
                $this->addCategory($castMember);
            }
        }

        // atualiza o updatedAt com a data atual
        if (
            isset($title) or
            isset($description) or
            isset($yearLaunched) or
            isset($duration) or
            isset($opened) or
            isset($rating) or
            isset($categoriesId) or
            isset($genresId) or
            isset($castMembersId)
        ) $this->updatedAt = new DateTime();

        // validando os atributos
        $this->validate();
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do title
        DomainValidation::notNullOrEmpty($this->title);
        DomainValidation::strMaxLenght($this->title);
        DomainValidation::strMinLenght($this->title);

        // validação do description
        DomainValidation::notNullOrEmpty($this->description);
        DomainValidation::strMaxLenght($this->description);
        DomainValidation::strMinLenght($this->description);

        // validação do yearLaunched
        DomainValidation::notNullOrZero($this->yearLaunched);

        // validação do duration
        DomainValidation::notNullOrZero($this->duration);

        // validação do rating
        if (is_string($this->rating)) {
            DomainValidation::isRatingCompatible($this->rating);
            if (Rating::tryFrom($this->rating)) $this->rating = Rating::from($this->rating);
        }
    }
}
