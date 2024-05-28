<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Events;

// importações
use Core\Domain\Entity\Video;

// definindo o evento
class VideoCreatedEvent implements EventInterface
{
    // construtor e atributos
    public function __construct(
        protected Video $video
    ) {
    }

    // função para retorno do nome do evento
    public function getEventName(): string
    {
        return 'video.created';
    }

    // função para retorno dos dados do evento
    public function getPayload(): array
    {
        return [
            'resource_id' => $this->video->id(),
            'file_path' => $this->video->videoFile()->filePath(),
        ];
    }
}
