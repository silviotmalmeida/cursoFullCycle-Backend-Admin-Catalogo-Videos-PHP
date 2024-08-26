<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Enum;

// definindo a enumeração (novo tipo, que possui um número fixo e limitado de valores legais possíveis)
enum ImageType: int
{
        // definindo os valores possíveis
    case THUMBFILE = 1;
    case THUMBHALF  = 2;
    case BANNERFILE  = 3;
}
