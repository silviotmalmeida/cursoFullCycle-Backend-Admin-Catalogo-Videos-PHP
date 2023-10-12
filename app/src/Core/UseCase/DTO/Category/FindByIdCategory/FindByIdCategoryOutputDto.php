<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Category\FindByIdCategory;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class FindByIdCategoryOutputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public bool $is_active,
        public string $created_at,
        public string $updated_at,
    ) {
    }
}
