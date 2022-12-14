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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'App\Http\Controllers\Api\AuthController@login')->name('login');
    Route::post('signup', 'App\Http\Controllers\Api\AuthController@signup');
    Route::post('googleAuth', 'App\Http\Controllers\Api\AuthController@googleAuth');
});
Route::post('inbrainCallback','App\Http\Controllers\Api\AdminController@inBrainsCallback');
Route::get('adJoeCallback','App\Http\Controllers\Api\AdminController@adJoeCallback');



Route::group(['middleware' => 'auth:api', 'namespace'], function() {
    Route::get('logout', 'App\Http\Controllers\Api\AuthController@logout');
    Route::get('user', 'App\Http\Controllers\Api\AuthController@user');
    Route::get('cards', 'App\Http\Controllers\Api\AdminController@getGiftCards');
    Route::post('cards/create', 'App\Http\Controllers\Api\AdminController@createGiftCard');
    Route::post('user/addPoints', 'App\Http\Controllers\Api\AdminController@addPoints');
    Route::post('generateCard', 'App\Http\Controllers\Api\AdminController@generateEgifterCard');
});
