<?php

namespace Tests\Feature\App\Services;

use App\Services\Storage\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store_delete()
    {
        // criando arquivo fake
        $fakeFile = UploadedFile::fake()->create('video.mp4', 1, 'video/mp4');

        // carregando os dados do fakeFile em um array $_FILES padrão php
        $file = [
            'name' => $fakeFile->getFilename(),
            'type' => $fakeFile->getMimeType(),
            'tmp_name' => $fakeFile->getPathname(),
            'error' => $fakeFile->getError(),
            'size' => $fakeFile->getSize(),
        ];

        // armazenando o arquivo
        $filePath = (new FileStorage())->store('videos', $file);

        // verificando
        Storage::assertExists($filePath);

        // apagando o arquivo
        Storage::delete($filePath);
    }

    public function test_delete()
    {
        // criando arquivo fake
        $fakeFile = UploadedFile::fake()->create('video.mp4', 1, 'video/mp4');

        // carregando os dados do fakeFile em um array $_FILES padrão php
        $file = [
            'name' => $fakeFile->getFilename(),
            'type' => $fakeFile->getMimeType(),
            'tmp_name' => $fakeFile->getPathname(),
            'error' => $fakeFile->getError(),
            'size' => $fakeFile->getSize(),
        ];

        // armazenando o arquivo
        $filePath = (new FileStorage())->store('videos', $file);

        // verificando
        Storage::assertExists($filePath);

        // apagando o arquivo
        $delete = (new FileStorage())->delete($filePath);

        // verificando
        Storage::assertMissing($filePath);
        $this->assertTrue($delete);
    }
}
