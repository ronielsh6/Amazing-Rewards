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
Route::post('inbrainCallback', 'App\Http\Controllers\Api\AdminController@inBrainsCallback');
Route::post('pollfishCallback', 'App\Http\Controllers\Api\AdminController@pollfishCallback');
Route::post('adGemCallback', 'App\Http\Controllers\Api\AdminController@adGemCallback');
Route::get('adJoeCallback', 'App\Http\Controllers\Api\AdminController@adJoeCallback');
Route::get('egifterOrders', 'App\Http\Controllers\Api\AdminController@getEgifterOrders');
Route::post('sendCustomNotification', 'App\Http\Controllers\Api\AdminController@sendCustomNotification');

Route::group(['middleware' => 'auth:api', 'namespace'], function () {
    Route::get('logout', 'App\Http\Controllers\Api\AuthController@logout');
    Route::get('user', 'App\Http\Controllers\Api\AuthController@user');
    Route::get('cards', 'App\Http\Controllers\Api\AdminController@getGiftCards');
    Route::post('cards/create', 'App\Http\Controllers\Api\AdminController@createGiftCard');
    Route::post('user/addPoints', 'App\Http\Controllers\Api\AdminController@addPoints');
    Route::post('user/spinResult', 'App\Http\Controllers\Api\AdminController@spinResult');
    Route::post('user/addSpin', 'App\Http\Controllers\Api\AdminController@addSpin');
    Route::post('user/updateFcmToken', 'App\Http\Controllers\Api\AdminController@updateFcmToken');
    Route::post('user/requestEmailCode', 'App\Http\Controllers\Api\AdminController@sendVerificationCode');
    Route::post('user/verifyEmail', 'App\Http\Controllers\Api\AdminController@verifyEmail');
    Route::post('user/updateLockScreenPermission', 'App\Http\Controllers\Api\AdminController@updateLockScreenPermission');
    Route::get('user/points-logs', 'App\Http\Controllers\Api\AdminController@getPointsLogs');
    Route::post('user/redeem-code', 'App\Http\Controllers\Api\AdminController@redeemCode');
});

Route::group(['middleware' => 'guest:api', 'namespace'], function () {
    Route::post('forgot', 'App\Http\Controllers\Api\AuthController@forgot');
});
