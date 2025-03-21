<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProviderStatisticController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
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
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/verify-forgot-password', 'verifyForgotPassword');
    });

    Route::controller(UserController::class)->prefix('users')->group(function ($router) {
        Route::get('/me', 'me');
        Route::get('/orders', 'getOrder');
        Route::put('/orders/{id}/update-status', 'updateStatusOrder');
        Route::get('/{id}', 'getById');
        Route::post('/uploadAvatar', 'uploadAvatar');
        Route::post('/uploadImage', 'uploadImage');
        Route::delete('/deleteImage', 'deleteImage');
        Route::put('/update', 'update');
        Route::put('/updatePassword', 'updatePassword');
        Route::post("/setting", "changeSetting");
        Route::delete('/viewed/{id}', 'deleteViewedServiceByServiceId');
        Route::delete('/delete/viewed/all', 'deleteAllViewedService');
    });

    Route::controller(ServiceController::class)->prefix('services')->group(function ($router) {
        Route::get('/', 'getAll');
        Route::get('/nearby', 'getNearbyServices');
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

    Route::controller(ProvinceController::class)->prefix('provinces')->group(function ($router) {
        Route::get('/', 'index');
        Route::get('/search', 'search');
        Route::get('/{id}', 'show');
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
        Route::get('/independent/{serviceId}', 'getIndependent');
        Route::delete('/{id}', 'delete');
    });

    Route::controller(FavoriteController::class)->prefix('favorites')->group(function () {
        Route::get('/', 'getFavorites');
        Route::post('/{serviceId}', 'toggleFavorite');
        Route::get('/list', 'getListFavorite');
    });

    Route::controller(CommentController::class)->prefix('comments')->group(function () {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/{serviceId}', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    // Add route to get comments by service ID
    Route::get('/services/{id}/comments', [CommentController::class, 'getCommentsByServiceId']);

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/', 'getAll');
        Route::get('/{id}', 'getById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'delete');
    });

    Route::controller(ChatbotController::class)->prefix('chatbot')->group(function () {
        Route::post('/run', 'run');
    });
});

Route::middleware(['auth:api'])->prefix('provider')->group(function () {
    // Quản lý dịch vụ
    Route::controller(ServiceController::class)->prefix('services')->group(function () {
        Route::get('/my-services', 'getMyServices');
        Route::get('/category-counts', 'getCategoryCounts');
        Route::get('/{id}', 'getProviderServiceById');
        Route::post('/', 'create');
        Route::put('/{id}', 'update');
        Route::delete('/{id}/{force?}', 'delete');

        // Quản lý giá dịch vụ
        Route::post('/{serviceId}/prices', 'addServicePrice');
        Route::put('/{serviceId}/prices/{priceId}', 'updateServicePrice');
        Route::delete('/{serviceId}/prices/{priceId}', 'deleteServicePrice');
    });

    // Quản lý đơn hàng
    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('/my-orders', 'getProviderOrders');
        Route::put('/{id}/status', 'updateOrderStatus');
    });

    // Quản lý đánh giá
    Route::controller(CommentController::class)->prefix('comments')->group(function () {
        Route::get('/my-services', 'getServiceComments');
        Route::post('/{commentId}/reply', 'replyToComment');
    });

    // Thống kê
    Route::controller(ProviderStatisticController::class)->prefix('statistics')->group(function () {
        Route::get('/', 'getStatistics');
    });

    // Quản lý location
    Route::controller(LocationController::class)->prefix('locations')->group(function ($router) {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    // Route cho benefits
    Route::controller(BenefitController::class)->prefix('benefits')->group(function () {
        Route::post('/create-with-prices', 'createWithPrices');
        Route::put('/{id}/update-with-prices', 'updateWithPrices');
        Route::post('/bulk-update', 'bulkUpdate');
        Route::get('/independent/{serviceId}', 'getIndependent');
        Route::delete('/{id}', 'delete');
    });

    // Route cho prices
    Route::controller(PriceController::class)->prefix('prices')->group(function () {
        Route::post('/create-with-benefits', 'createWithBenefits');
        Route::put('/{id}/update-with-benefits', 'updateWithBenefits');
        Route::post('/bulk-update', 'bulkUpdate');
    });



});

// Report routes
Route::post('/reports', 'App\Http\Controllers\ReportController@store');
Route::get('/reports', 'App\Http\Controllers\ReportController@index');
Route::get('/reports/{id}', 'App\Http\Controllers\ReportController@show');
Route::patch('/reports/{id}', 'App\Http\Controllers\ReportController@update');

