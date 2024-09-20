<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Interfaces;

// definindo a interface de armazenamento de arquivos
interface FileStorageInterface
{
    /**
     * @param string $path
     * @param array $_FILES[file]
     * atributos presentes neste array:
     * name
     * type
     * tmp_name
     * error
     * size 
     */
    public function store(string $path, array $file): string;

    public function delete(string $path): bool;
}
