<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Stubs;

// importações
use Core\UseCase\Video\Interfaces\VideoEventManagerInterface;

// definindo o stub(mock) do gerenciador de eventos para utilização nos testes
class VideoEventManagerStub implements VideoEventManagerInterface
{
    public function dispatch(object $event): void
    {
        // repassando a classe como evento
        event($this);
    }
}