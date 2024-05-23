<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\ValueObject;

// importações
use Core\Domain\Notification\Notification;

// definindo a classe abstrata
// classe-mãe de todas os objetos de valor
abstract class ValueObject
{
    // atributos
    protected $notification;

    // construtor
    public function __construct()
    {
        // carregando o agregador de notificações
        $this->notification = new Notification();
    }
}
