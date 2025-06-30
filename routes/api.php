<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\TaskController;
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
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'subtask', 'controller' => SubtaskController::class], function () {
        Route::get('/index/{task_id}', 'index');
        Route::post('/store', 'store');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/token', [PaymentController::class, 'createSnapToken']);
});

Route::post('/payment/callback', [PaymentController::class, 'midtransCallback']);