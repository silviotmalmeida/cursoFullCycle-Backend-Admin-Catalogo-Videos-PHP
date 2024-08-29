<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Enum;

// definindo a enumeração (novo tipo, que possui um número fixo e limitado de valores legais possíveis)
enum ImageType: int
{
        // definindo os valores possíveis
    case THUMB = 1;
    case THUMB_HALF  = 2;
    case BANNER  = 3;
}
