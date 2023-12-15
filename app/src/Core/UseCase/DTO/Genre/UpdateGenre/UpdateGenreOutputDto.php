<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Genre\UpdateGenre;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class UpdateGenreOutputDto
{
    // construtor e atributos
    public function __construct(
        public string $id,
        public string $name,
        public bool $is_active,
        public array $categories_id,
        public string $created_at,
        public string $updated_at,
    ) {
    }
}
