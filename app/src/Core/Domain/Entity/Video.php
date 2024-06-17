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
    // construtor e atributos
    public function __construct(
        protected Uuid|string $id = '',
        protected string $title = '',
        protected string $description = '',
        protected int $yearLaunched = 0,
        protected int $duration = 0,
        protected bool $opened = false,
        protected Rating|string $rating = '',
        protected array $categoriesId = [],
        protected array $genresId = [],
        protected array $castMembersId = [],
        protected ?Image $thumbFile = null,
        protected ?Image $thumbHalf = null,
        protected ?Image $bannerFile = null,
        protected ?Media $trailerFile = null,
        protected ?Media $videoFile = null,
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

    // função para retorno do thumbHalf
    public function thumbHalf(): ?Image
    {
        return $this->thumbHalf;
    }

    // função para retorno do bannerFile
    public function bannerFile(): ?Image
    {
        return $this->bannerFile;
    }

    // função para retorno do trailerFile
    public function trailerFile(): ?Media
    {
        return $this->trailerFile;
    }

    // função para retorno do videoFile
    public function videoFile(): ?Media
    {
        return $this->videoFile;
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

    // // função de atualização dos atributos possíveis
    // public function update(
    //     ?string $title = null,
    //     ?string $description = null,
    //     ?int $yearLaunched = null,
    //     ?int $duration = null,
    //     ?bool $opened = null,
    //     ?array $categoriesId = null,
    //     ?array $genresId = null,
    //     ?array $castMembersId = null,
    //     ?Rating $rating = null,
    //     ?Image $thumbFile = null,
    //     ?Image $thumbHalf = null,
    //     ?Image $bannerFile = null,
    //     ?Media $trailerFile = null,
    //     ?Media $videoFile = null,
    // ): void {
    //     // atualiza somente os atributos com valores recebidos
    //     if (isset($title)) $this->title = $title;
    //     if (isset($description)) $this->description = $description;
    //     if (isset($yearLaunched)) $this->yearLaunched = $yearLaunched;
    //     if (isset($duration)) $this->duration = $duration;

    //     if (isset($opened)) {
    //         if ($opened === true) {
    //             $this->open();
    //         } else if ($opened === false) {
    //             $this->close();
    //         }
    //     }

    //     if (isset($categoriesId)) {
    //         // removendo as categorias previamente cadastradas
    //         foreach ($this->categoriesId as $category) {
    //             $this->removeCategory($category);
    //         }
    //         // inserindo as novas categorias
    //         foreach ($categoriesId as $category) {
    //             $this->addCategory($category);
    //         }
    //     }

    //     if (isset($genresId)) {
    //         // removendo os genres previamente cadastrados
    //         foreach ($this->genresId as $genre) {
    //             $this->removeCategory($genre);
    //         }
    //         // inserindo os novos genres
    //         foreach ($genresId as $genre) {
    //             $this->addCategory($genre);
    //         }
    //     }

    //     if (isset($castMembersId)) {
    //         // removendo os cast members previamente cadastrados
    //         foreach ($this->castMembersId as $castMember) {
    //             $this->removeCategory($castMember);
    //         }
    //         // inserindo os novos cast members
    //         foreach ($castMembersId as $castMember) {
    //             $this->addCategory($castMember);
    //         }
    //     }

    //     if (isset($rating)) $this->rating = $rating;
    //     if (isset($thumbFile)) $this->thumbFile = $thumbFile;
    //     if (isset($thumbHalf)) $this->thumbHalf = $thumbHalf;
    //     if (isset($bannerFile)) $this->bannerFile = $bannerFile;
    //     if (isset($trailerFile)) $this->trailerFile = $trailerFile;
    //     if (isset($videoFile)) $this->videoFile = $videoFile;

    //     // atualiza o updatedAt com a data atual
    //     if (
    //         isset($title) or
    //         isset($description) or
    //         isset($yearLaunched) or
    //         isset($duration) or
    //         isset($opened) or
    //         isset($categoriesId) or
    //         isset($genresId) or
    //         isset($castMembersId) or
    //         isset($rating) or
    //         isset($thumbFile) or
    //         isset($thumbHalf) or
    //         isset($thumbFile) or
    //         isset($bannerFile) or
    //         isset($trailerFile) or
    //         isset($videoFile)
    //     ) $this->updatedAt = new DateTime();

    //     // validando os atributos
    //     $this->validate();
    // }

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
