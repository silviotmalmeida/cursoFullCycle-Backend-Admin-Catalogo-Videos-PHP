<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Video\Paginate\DTO;

// definindo o dto de entrada do usecase (basicamente uma classe anêmica com atributos públicos)
class PaginateVideoInputDto
{
    // construtor e atributos
    public function __construct(
        public string $filter = '',
        public string $order = 'DESC',
        public int $page = 1,
        public int $perPage = 15,
    ) {
    }
}
