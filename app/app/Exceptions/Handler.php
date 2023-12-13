<?php

namespace App\Exceptions;

use Core\Domain\Exception\EntityValidationException;
use Core\Domain\Exception\NotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // 
        });
    }

    // sobreescrevendo o método para interceptar as exceções
    public function render($request, Throwable $exception)
    {
        // interceptando a NotFoundException
        if ($exception instanceof NotFoundException) return $this->showError($exception->getMessage(), Response::HTTP_NOT_FOUND);

        // interceptando a EntityValidationException
        if ($exception instanceof EntityValidationException) return $this->showError($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);

        // executando o método original
        return parent::render($request, $exception);
    }

    // função auxiliar para envio da mensagem de erro
    private function showError(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $statusCode);
    }
}
