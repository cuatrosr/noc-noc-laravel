<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\Profile\PasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register'])->middleware(['role:admin']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('verify-email', [AuthController::class, 'verifyUserEmail']);
    Route::post('resend-email-verification-link', [AuthController::class, 'resendEmailVerificationLink']);
    Route::post('change-password', [AuthController::class, 'changeUserPassword']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'tasks'
], function () {
    Route::post('create', [TaskController::class, 'create'])->middleware(['role:admin']);
    Route::get('index', [TaskController::class, 'index']);
    Route::get('show/{id}', [TaskController::class, 'show']);
    Route::put('update/{id}', [TaskController::class, 'update'])->middleware(['role:admin']);
    Route::delete('delete/{id}', [TaskController::class, 'delete'])->middleware(['role:admin']);
    Route::put('update-status/{id}', [TaskController::class, 'updateStatus']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'comments'
], function () {
    Route::post('create', [CommentController::class, 'create']);
    Route::get('index', [CommentController::class, 'index']);
    Route::get('show/{id}', [CommentController::class, 'show']);
    Route::put('update/{id}', [CommentController::class, 'update']);
    Route::delete('delete/{id}', [CommentController::class, 'delete']);
});
