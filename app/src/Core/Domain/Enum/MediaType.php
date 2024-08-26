<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Enum;

// definindo a enumeração (novo tipo, que possui um número fixo e limitado de valores legais possíveis)
enum MediaType: int
{
        // definindo os valores possíveis
    case VIDEO = 1;
    case TRAILER  = 2;
}
