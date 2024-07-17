<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Builder\Video;

// importações
use Core\Domain\Entity\Video;
use Core\Domain\Enum\MediaStatus;

// definindo a interface especializada de construção de entidades complexas
interface VideoBuilderInterface
{
    public function createEntity(object $input): VideoBuilderInterface;
    public function addThumbFile(string $path): VideoBuilderInterface;
    public function addThumbHalf(string $path): VideoBuilderInterface;
    public function addBannerFile(string $path): VideoBuilderInterface;
    public function addTrailerFile(string $path, MediaStatus $mediaStatus): VideoBuilderInterface;
    public function addVideoFile(string $path, MediaStatus $mediaStatus): VideoBuilderInterface;
    public function getEntity(): Video;
}
