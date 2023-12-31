<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Category\UpdateCategory;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateCategoryInputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $description,
        public ?bool $isActive,
    ) {
    }
}
