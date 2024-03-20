<?php

// importações

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GenreController;
use Illuminate\Support\Facades\Route;

// definindo rota de healthcheck
Route::get('/', function () {
    return response()->json(['message' => 'success']);
});
// definindo rotas de categories
Route::apiResource('/categories', CategoryController::class);
// definindo rotas de genres
Route::apiResource('/genres', GenreController::class);
