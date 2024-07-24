<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Services\Storage;

// importações
use Core\UseCase\Interfaces\FileStorageInterface;

// definindo a classe
class FileStorage implements FileStorageInterface
{
    /**
     * @param string $path
     * @param array $_FILES[file]
     */
    public function store(string $path, array $file): string
    {
        return '';
    }

    public function delete(string $path): bool
    {
        return false;
    }
}
