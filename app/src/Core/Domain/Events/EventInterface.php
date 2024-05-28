<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Events;

// definindo a interface a ser implementada pelos eventos
interface EventInterface
{
    public function getEventName(): string;
    public function getPayload(): array;
}
