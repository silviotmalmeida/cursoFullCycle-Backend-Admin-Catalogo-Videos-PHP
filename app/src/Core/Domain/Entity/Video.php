<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Enum\Rating;
use Core\Domain\Factory\VideoValidatorFactory;
use Core\Domain\Notification\NotificationException;
use Core\Domain\Validation\DomainValidation;
use Core\Domain\ValueObject\Image;
use Core\Domain\ValueObject\Media;
use Core\Domain\ValueObject\Uuid;
use DateTime;

// definindo a entidade
class Video extends Entity
{
    // atributos fora do construtor
    protected bool $opened = false;
    protected array $categoriesId = [];
    protected array $genresId = [];
    protected array $castMembersId = [];
    protected ?Image $thumbFile = null;
    protected ?Image $thumbHalf = null;
    protected ?Image $bannerFile = null;
    protected ?Media $trailerFile = null;
    protected ?Media $videoFile = null;

    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $title = '',
        protected string $description = '',
        protected int $yearLaunched = 0,
        protected int $duration = 0,
        protected Rating|string $rating = '',
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

    // função para retorno do thumbFile
    public function thumbFile(): ?Image
    {
        return $this->thumbFile;
    }

    // função para set do thumbFile
    public function setThumbFile(Image $thumbFile): void
    {
        $this->thumbFile = $thumbFile;
    }

    // função para retorno do thumbHalf
    public function thumbHalf(): ?Image
    {
        return $this->thumbHalf;
    }

    // função para set do thumbHalf
    public function setThumbHalf(Image $thumbHalf): void
    {
        $this->thumbHalf = $thumbHalf;
    }

    // função para retorno do bannerFile
    public function bannerFile(): ?Image
    {
        return $this->bannerFile;
    }

    // função para set do bannerFile
    public function setBannerFile(Image $bannerFile): void
    {
        $this->bannerFile = $bannerFile;
    }

    // função para retorno do trailerFile
    public function trailerFile(): ?Media
    {
        return $this->trailerFile;
    }

    // função para set do trailerFile
    public function setTraileFile(Media $trailerFile): void
    {
        $this->trailerFile = $trailerFile;
    }

    // função para retorno do videoFile
    public function videoFile(): ?Media
    {
        return $this->videoFile;
    }

    // função para set do videoFile
    public function setVideoFile(Media $videoFile): void
    {
        $this->videoFile = $videoFile;
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

    // função de atribuição de categoryId
    public function addCategoryId(Uuid|string $categoryId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($categoryId instanceof Uuid)) $categoryId = new Uuid($categoryId);
        // adicionando no array de categories
        if (!(in_array($categoryId, $this->categoriesId))) array_push($this->categoriesId, $categoryId);
        // removendo duplicatas
        $this->categoriesId = array_unique($this->categoriesId);
    }

    // função de remoção de categoryId
    public function removeCategoryId(Uuid|string $categoryId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($categoryId instanceof Uuid)) $categoryId = new Uuid($categoryId);
        // removendo do array de categories
        if (in_array($categoryId, $this->categoriesId)) $this->categoriesId = array_diff($this->categoriesId, [$categoryId]);
        // removendo duplicatas
        $this->categoriesId = array_unique($this->categoriesId);
    }

    // função de atribuição de genreId
    public function addGenreId(Uuid|string $genreId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($genreId instanceof Uuid)) $genreId = new Uuid($genreId);
        // adicionando no array de genres
        if (!(in_array($genreId, $this->genresId))) array_push($this->genresId, $genreId);
        // removendo duplicatas
        $this->genresId = array_unique($this->genresId);
    }

    // função de remoção de genreId
    public function removeGenreId(Uuid|string $genreId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($genreId instanceof Uuid)) $genreId = new Uuid($genreId);
        // removendo do array de genres
        if (in_array($genreId, $this->genresId)) $this->genresId = array_diff($this->genresId, [$genreId]);
        // removendo duplicatas
        $this->genresId = array_unique($this->genresId);
    }

    // função de atribuição de castMemberId
    public function addCastMemberId(Uuid|string $castMemberId): void
    {
        // caso não seja uuid, valida a string informada
        if (!($castMemberId instanceof Uuid)) $castMemberId = new Uuid($castMemberId);
        // adicionando no array de cast members
        if (!(in_array($castMemberId, $this->castMembersId))) array_push($this->castMembersId, $castMemberId);
        // removendo duplicatas
        $this->castMembersId = array_unique($this->castMembersId);
    }

    // função de remoção de castMemberId
    public function removeCastMemberId(Uuid|string $castMemberId): void
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
        ?array $categoriesId = null,
        ?array $genresId = null,
        ?array $castMembersId = null,
        ?Rating $rating = null,
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

        if (isset($categoriesId)) {
            // removendo as categorias previamente cadastradas
            foreach ($this->categoriesId as $category) {
                $this->removeCategoryId($category);
            }
            // inserindo as novas categorias
            foreach ($categoriesId as $category) {
                $this->addCategoryId($category);
            }
        }

        if (isset($genresId)) {
            // removendo os genres previamente cadastrados
            foreach ($this->genresId as $genre) {
                $this->removeGenreId($genre);
            }
            // inserindo os novos genres
            foreach ($genresId as $genre) {
                $this->addGenreId($genre);
            }
        }

        if (isset($castMembersId)) {
            // removendo os cast members previamente cadastrados
            foreach ($this->castMembersId as $castMember) {
                $this->removeCastMemberId($castMember);
            }
            // inserindo os novos cast members
            foreach ($castMembersId as $castMember) {
                $this->addCastMemberId($castMember);
            }
        }

        if (isset($rating)) $this->rating = $rating;

        // atualiza o updatedAt com a data atual
        if (
            isset($title) or
            isset($description) or
            isset($yearLaunched) or
            isset($duration) or
            isset($opened) or
            isset($categoriesId) or
            isset($genresId) or
            isset($castMembersId) or
            isset($rating)
        ) $this->updatedAt = new DateTime();

        // validando os atributos
        $this->validate();
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // Validação utilizando o Laravel e agregador de notificação
        // utilizado em validações genéricas
        // 
        VideoValidatorFactory::create()->validate($this);

        if ($this->notification->hasErrors()) {
            throw new NotificationException(
                $this->notification->messages('video')
            );
        }

        // Validação utilizando o DomainValidation e sem agregador de notificação
        // utilizado em validações específicas
        // 
        // validação do rating
        if (is_string($this->rating)) {
            DomainValidation::isRatingCompatible($this->rating);
            if (Rating::tryFrom($this->rating)) $this->rating = Rating::from($this->rating);
        }
    }
}
