<?php

// importações

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// definindo rota de healthcheck
Route::get('/', function () {
    return response()->json(['message' => 'success']);
});
// definindo rotas de categories
Route::apiResource('/categories', CategoryController::class);
