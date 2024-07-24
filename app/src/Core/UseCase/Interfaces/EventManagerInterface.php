<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Interfaces;

// definindo a interface genérica de gerenciamento de eventos
interface EventManagerInterface
{
    public function dispatch(object $event): void;
}
