<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\HotelController;
use App\Http\Controllers\API\RoomController;


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [LoginController::class, 'Login']);
    Route::post('/logout', [LoginController::class, 'Logout']);
});

// Route::middleware('auth:api')->group(function () {
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'hotels'
], function () {
    Route::get('/', [HotelController::class, 'index']);
    Route::post('/', [HotelController::class, 'store']);
    Route::get('/detail/{slug}', [HotelController::class, 'show']);
    Route::get('/{id}', [HotelController::class, 'edit']);
    Route::put('/{id}', [HotelController::class, 'update']);
    Route::delete('/{id}', [HotelController::class, 'destroy']);
});

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'rooms'
], function () {
    Route::get('/{id}', [RoomController::class, 'show']);
    Route::post('/', [RoomController::class, 'store']);
    Route::put('/{id}', [RoomController::class, 'update']);
    Route::delete('/{id}', [RoomController::class, 'destroy']);
});

