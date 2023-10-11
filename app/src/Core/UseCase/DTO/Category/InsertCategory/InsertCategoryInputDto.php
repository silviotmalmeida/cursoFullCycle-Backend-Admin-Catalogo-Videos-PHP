<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Category\InsertCategory;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class InsertCategoryInputDto
{
    // construtor e atributos
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $isActive = true,
    ) {
    }
}
