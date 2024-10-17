<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Services\Storage;

// importações
use Core\UseCase\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// definindo a classe
class FileStorage implements FileStorageInterface
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
    public function store(string $path, array $file): string
    {
        // convertendo para o padrão do Laravel
        $contents = $this->convertFileToLaravelFile($file);
        // armazenando o arquivo
        return Storage::put($path, $contents);
    }

    public function delete(string $path): bool
    {
        // apagando o arquivo armazenado
        return Storage::delete($path);
    }

    // função auxiliar para converter o array $_FILES nativo do PHP para o padrão do Laravel
    protected function convertFileToLaravelFile(array $file): UploadedFile
    {
        return new UploadedFile(
            originalName: $file['name'],
            mimeType: $file['type'],
            path: $file['tmp_name'],
            error: $file['error']
        );
    }
}
