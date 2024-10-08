<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Core\Domain\Validation\DomainValidation;

// definindo o objeto de valor
class Media extends ValueObject
{
    // construtor e atributos
    public function __construct(
        protected string $filePath = '',
        protected MediaStatus|int $mediaStatus = 0,
        protected MediaType|int $mediaType = 0,
        protected string $encodedPath = '',
    ) {
        // incluindo as regras do médoto de criação da classe-mãe
        parent::__construct();

        // validando os atributos
        $this->validate();
    }

    // função de obtenção do filePath
    public function filePath(): string
    {
        return $this->filePath;
    }

    // função de obtenção do mediaStatus
    public function mediaStatus(): MediaStatus
    {
        return $this->mediaStatus;
    }

    // função de obtenção do mediaType
    public function mediaType(): MediaType
    {
        return $this->mediaType;
    }

    // função de obtenção do encodedPath
    public function encodedPath(): string
    {
        return $this->encodedPath;
    }

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do filePath
        DomainValidation::notNullOrEmpty($this->filePath);

        // validação do mediaStatus
        if (is_int($this->mediaStatus)) {
            DomainValidation::isMediaStatusCompatible($this->mediaStatus);
            if (MediaStatus::tryFrom($this->mediaStatus)) $this->mediaStatus = MediaStatus::from($this->mediaStatus);
        }

        // validação do mediaType
        if (is_int($this->mediaType)) {
            DomainValidation::isMediaTypeCompatible($this->mediaType);
            if (MediaType::tryFrom($this->mediaType)) $this->mediaType = MediaType::from($this->mediaType);
        }
    }
}
