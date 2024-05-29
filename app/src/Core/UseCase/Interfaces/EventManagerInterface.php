<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Interfaces;

// definindo a interface genérica de gerenciamento de eventos
interface EventManagerInterface
{
    /**
     * @param string $path
     * @param array $_FILES[file]
     */
    public function dispatch(object $event): void;
}
