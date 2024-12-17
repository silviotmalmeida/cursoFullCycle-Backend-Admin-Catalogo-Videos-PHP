<?php

// definindo o namespace, referente ao caminho das pastas
namespace Tests\Traits;

// definindo a trait que vai desativar os middlewares de autenticação nos testes
trait DisableAuthMiddlewareTrait
{
    // sobreescrevendo o método setUp para desativar os middlewares de autenticação nos testes
    protected function setUp(): void
    {
        parent::setUp();

        // desabilitando middlewares de autenticação
        $this->withoutMiddleware([
            \App\Http\Middleware\Authenticate::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    }
}
