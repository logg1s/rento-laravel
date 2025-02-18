<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function ($router) {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/me', 'me');
    Route::post('/register', 'register');
    Route::get('/validate', 'validateToken');
});

Route::controller(ServiceController::class)->prefix('services')->group(function ($router) {
    Route::get('/', 'getAll');
    Route::get('/{id}', 'getById');
    Route::post('/', 'create');
    Route::put('/{id}', 'update');
    Route::delete('/{id}/{force?}', 'delete');
    Route::get('/restore/{id}', 'restore');
});

Route::controller(PriceController::class)->prefix('prices')->group(function ($router) {
    Route::get('/', 'getAll');
    Route::get('/{id}', 'getById');
    Route::post('/', 'create');
    Route::post('/{id}', 'update');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'delete');
});
