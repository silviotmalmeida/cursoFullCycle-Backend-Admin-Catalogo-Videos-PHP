<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Paginate\DTO;

// definindo o dto de saída do usecase (basicamente uma classe anêmica com atributos públicos)
class PaginateVideoOutputDto
{
    // construtor e atributos
    public function __construct(
        public array $items,
        public int $total,
        public int $last_page,
        public int $first_page,
        public int $current_page,
        public int $per_page,
        public int $to,
        public int $from,
    ) {
    }
}
