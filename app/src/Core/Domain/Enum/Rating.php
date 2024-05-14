<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Enum;

// definindo a enumeração (novo tipo, que possui um número fixo e limitado de valores legais possíveis)
enum Rating: string
{
        // definindo os valores possíveis
    case ER  = 'ER';
    case L = 'L';
    case RATE10 = '10';
    case RATE12 = '12';
    case RATE14 = '14';
    case RATE16 = '16';
    case RATE18 = '18';
}
