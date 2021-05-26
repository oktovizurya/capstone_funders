<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [App\Http\Controllers\AuthController ::class, 'login']);
    Route::post('/register', [App\Http\Controllers\AuthController ::class, 'register']);
    Route::get('/get_role', [App\Http\Controllers\AuthController ::class, 'get_role']);
    Route::get('/get_all_provinsi', [App\Http\Controllers\AuthController::class, 'get_all_provinsi']);
    Route::get('/get_provinsi_kabkota', [App\Http\Controllers\AuthController::class, 'get_provinsi_kabkota']);
    Route::get('/get_all_user', [App\Http\Controllers\AuthController::class, 'get_all_user']);
    Route::get('get_status', [App\Http\Controllers\AuthController::class, 'get_status']);

    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/refresh_token', [App\Http\Controllers\AuthController::class, 'refresh_token']);
    Route::get('/user_profile', [App\Http\Controllers\AuthController::class, 'user_profile']);

    Route::post('/update_profile', [App\Http\Controllers\AuthController::class, 'update_profile']);
    Route::post('/update_dataset', [App\Http\Controllers\AuthController::class, 'update_dataset']);
});
