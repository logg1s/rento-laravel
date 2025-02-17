<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::prefix("auth")->middleware(["auth:api"])->group(
//     function () {
//         Route::post('/login', [AuthController::class, 'login']);
//         Route::post('/logout', [AuthController::class, 'logout']);
//         Route::post('/refresh', [AuthController::class, 'refresh']);
//         Route::get('/me',  [AuthController::class, 'me']);
//         Route::get("/hehe", [AuthController::class, 'register']);
//     }
// );
Route::controller(AuthController::class)->prefix('auth')->middleware('api')->group(function ($router) {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/me', 'me');
    Route::post('/register', 'register');
    Route::get('/validate', 'validateToken');
});
