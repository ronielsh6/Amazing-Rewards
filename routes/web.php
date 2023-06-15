<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => true, // Password Reset Routes...
    'verify' => true, // Email Verification Routes...
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/users', [App\Http\Controllers\HomeController::class, 'getUsers'])->name('getUsers');
Route::get('/blacklist', [App\Http\Controllers\HomeController::class, 'blacklist'])->name('blacklist');
Route::get('/blockedUsers', [App\Http\Controllers\HomeController::class, 'blockedUsers'])->name('blockedUsers');
Route::post('/user/delete', [App\Http\Controllers\HomeController::class, 'deleteUsers'])->name('deleteUser');

Route::get('/user/giftcards', [App\Http\Controllers\HomeController::class, 'getUserGiftCards'])->name('showGiftCards');
Route::get('/giftcards', [App\Http\Controllers\HomeController::class, 'getGiftCards'])->name('getGiftCards');
Route::get('/giftcards-logs', [App\Http\Controllers\HomeController::class, 'getGiftCardsLogs'])->name('getGiftCardsLogs');
Route::post('/giftcards/enable', [App\Http\Controllers\HomeController::class, 'getEnabledGiftCard'])->name('enableGiftcard');
Route::post('/send-messages', [App\Http\Controllers\HomeController::class, 'sendMessages'])->name('sendMessages');

//CAMPAIGN ROUTES
Route::get('/campaigns', [App\Http\Controllers\CampaignController::class, 'getCampaigns'])->name('campaigns');
Route::post('/campaigns/list', [App\Http\Controllers\CampaignController::class, 'getCampaignsList'])->name('campaignsList');
Route::post('/campaigns/create', [App\Http\Controllers\CampaignController::class, 'createCampaign'])->name('createCampaign');
Route::post('/campaigns/update', [App\Http\Controllers\CampaignController::class, 'updateCampaign'])->name('updateCampaign');
Route::post('/campaigns/delete', [App\Http\Controllers\CampaignController::class, 'deleteCampaign'])->name('deleteCampaign');
Route::post('/campaigns/execute', [App\Http\Controllers\CampaignController::class, 'executeCampaign'])->name('executeCampaign');
Route::post('/campaigns/queryImpact', [App\Http\Controllers\CampaignController::class, 'queryImpact'])->name('queryImpact');
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
Route::post('/uploads/process', [App\Http\Controllers\FileUploadController::class, 'process'])->name('uploads.process');
Route::delete('/uploads/remove', [App\Http\Controllers\FileUploadController::class, 'remove'])->name('uploads.remove');

//BLOCKED LOGS ROUTES
Route::get('/blocked-logs', [App\Http\Controllers\HomeController::class, 'getBlockedLogs'])->name('blockedLogs');
Route::get('/blocked-logs/list', [App\Http\Controllers\HomeController::class, 'getBlockedLogsList'])->name('blockedLogsList');
