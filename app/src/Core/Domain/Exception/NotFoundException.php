<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Exception;

// importações
use Exception;

// definindo a Exception customizada para ocorrências de not found no bd
class NotFoundException extends Exception
{
}
