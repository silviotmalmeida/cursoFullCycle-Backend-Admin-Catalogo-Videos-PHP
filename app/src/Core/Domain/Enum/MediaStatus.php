<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Enum;

// definindo a enumeração (novo tipo, que possui um número fixo e limitado de valores legais possíveis)
enum MediaStatus: int
{
        // definindo os valores possíveis
    case PENDING = 1;
    case PROCESSING  = 2;
    case COMPLETE = 3;
}
