<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações

use Core\Domain\Enum\ImageType;
use Core\Domain\Validation\DomainValidation;

// definindo o objeto de valor
class Image extends ValueObject
{
    // construtor e atributos
    public function __construct(
        protected string $filePath,
        protected ImageType|int $imageType = 0,
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

    // função de validação dos atributos
    private function validate(): void
    {
        // validação do filePath
        DomainValidation::notNullOrEmpty($this->filePath);

        // validação do imageType
        if (is_int($this->imageType)) {
            DomainValidation::isImageTypeCompatible($this->imageType);
            if (ImageType::tryFrom($this->imageType)) $this->imageType = ImageType::from($this->imageType);
        }
    }
}
