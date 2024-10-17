<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\DTO\Category\PaginateCategory;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class PaginateCategoryInputDto
{
    // construtor e atributos
    public function __construct(
        public ?string $filter = '',
        public string $order = 'ASC',
        public int $page = 1,
        public int $perPage = 15,
    ) {
    }
}
