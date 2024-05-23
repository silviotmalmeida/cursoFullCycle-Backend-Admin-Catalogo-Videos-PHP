<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Entity;

// importações
use Core\Domain\Entity\Traits\MagicMethodsTrait;
use Core\Domain\Notification\Notification;

// definindo a classe abstrata
// classe-mãe de todas as entidades
abstract class Entity
{
    // incluindo a trait que ativa os métodos mágicos
    use MagicMethodsTrait;

    // atributos
    protected $notification;

    // construtor
    public function __construct()
    {
        // carregando o agregador de notificações
        $this->notification = new Notification();
    }
}
