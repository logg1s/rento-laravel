<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function ($router) {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::post('/register', 'register');
        Route::get('/validate', 'validateToken');
        Route::post('/checkEmail', 'checkEmail');
        Route::post('/login-google', 'loginWithGoogle');
        Route::post('/verify-code', 'verifyCode');
        Route::post('/resend-verification', 'resendVerificationCode');
    });

    Route::controller(UserController::class)->prefix('users')->group(function ($router) {
        Route::get('/me', 'me');
        Route::get('/orders', 'getOrder');
        Route::put('/orders/{id}/update-status', 'updateStatusOrder');
        Route::get('/{id}', 'getById');
        Route::post('/uploadAvatar', 'uploadAvatar');
        Route::post('/uploadImage', 'uploadImage');
        Route::put('/update', 'update');
        Route::put('/updatePassword', 'updatePassword');
        Route::post("/setting", "changeSetting");
        Route::delete('/viewed/{id}', 'deleteViewedServiceByServiceId');
        Route::delete('/delete/viewed/all', 'deleteAllViewedService');
    });

    Route::controller(ServiceController::class)->prefix('services')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}/{force?}', 'delete');
        Route::get('/restore/{id}', 'restore');
    });

    Route::controller(\App\Http\Controllers\CategoryController::class)->prefix('categories')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/', 'create');
        Route::post('/chat/{id}', 'chatNotification');
        Route::put('/read/all', 'readedAll');
        Route::put('/readed/{id}', 'readedById');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
        Route::post('/token/register', 'registerToken');
        Route::delete('/token/delete', 'deleteToken');
    });

    Route::controller(PriceController::class)->prefix('prices')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::controller(BenefitController::class)->prefix('benefits')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::get('/service/{serviceId}', 'getByServiceId');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::controller(FavoriteController::class)->prefix('favorites')->group(function () {
        Route::get('/', 'getFavorites');
        Route::post('/{serviceId}', 'toggleFavorite');
    });

    Route::controller(CommentController::class)->prefix('comments')->group(function () {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/{serviceId}', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });
});
