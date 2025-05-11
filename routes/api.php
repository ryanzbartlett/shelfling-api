<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'version' => '0.0.1',
        'status' => 'ok',
    ]);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('/user', [AuthController::class, 'user'])
    ->middleware('auth:sanctum');
