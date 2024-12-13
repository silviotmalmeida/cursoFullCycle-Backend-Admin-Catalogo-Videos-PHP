<?php

// importações

use App\Http\Controllers\Api\CastMemberController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

// definindo rota de healthcheck
Route::get('/', function () {
    return response()->json(['message' => 'success']);
});

// rotas acessíveis após autenticação
// os usuários devem ter a role admin-catalog
Route::middleware(['auth:api', 'can:admin-catalog'])->group(function () {
    Route::get('/me', function () {
        return true;
    });    
});

// definindo rotas de categories
Route::apiResource('/categories', CategoryController::class);
// definindo rotas de genres
Route::apiResource('/genres', GenreController::class);
// definindo rotas de cast_members
Route::apiResource('/cast_members', CastMemberController::class);
// definindo rotas de videos
Route::apiResource('/videos', VideoController::class);
