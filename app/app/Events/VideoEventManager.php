<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Events;

// importações
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;

// definindo o gerenciador de eventos
class VideoEventManager implements VideoEventManagerInterface
{
    public function dispatch(object $event): void
    {
        // repassando o evento recebido
        event($event);
    }
}
