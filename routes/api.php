<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'version' => '0.0.1',
        'status' => 'ok',
    ]);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    Route::post('libraries', [LibraryController::class, 'store']);
    Route::get('libraries', [LibraryController::class, 'index']);
    Route::get('libraries/{library}', [LibraryController::class, 'show']);
    Route::put('libraries/{library}', [LibraryController::class, 'update']);
    Route::delete('libraries/{library}', [LibraryController::class, 'destroy']);
    Route::post('libraries/{library}/users', [LibraryController::class, 'addUsers']);
    Route::delete('libraries/{library}/users', [LibraryController::class, 'removeUser']);
    Route::get('libraries/{library}/users', [LibraryController::class, 'getUsers']);
});
