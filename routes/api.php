<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', fn() => auth()->user());

Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::middleware('auth:sanctum')->post('/logout', 'logout');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'task', 'controller' => TaskController::class], function () {
        Route::get('/index', 'index');
        Route::post('/store', 'store');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'delete');
        Route::put('/status/done/{id}', 'statusDone');
        Route::put('/status/pending/{id}', 'statusPending');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::delete('/user/delete-avatar', [UserController::class, 'deleteAvatar']);
    Route::post ('/user/change-password', [UserController::class, 'changePassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'subtask', 'controller' => SubtaskController::class], function () {
        Route::get('/index/{task_id}', 'index');
        Route::post('/store', 'store');
        Route::put('/update/{id}', 'update');
        Route::put('/{id}/update-status', 'updateStatus');
        Route::delete('/delete/{id}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/token', [PaymentController::class, 'createSnapToken']);
});

Route::post('/payment/callback', [PaymentController::class, 'midtransCallback']);
Route::get('/invoice/download/{snap_order_id}', [PaymentController::class, 'downloadInvoice']);
