<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\LocationAPIController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReactorCycleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserDetailsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->controller(ClinicController::class)->prefix('clinics')->group(function(){
    Route::get('name/{name?}', 'showByName');
    Route::middleware('admin')->group(function(){
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('download','downloadList');
        Route::get('dropdown', 'clinicDropdown');
        Route::get('{id}', 'show');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::patch('{id}/status', 'updateStatus');
    });
});

Route::middleware('auth:api')->controller(ReactorCycleController::class)->prefix('reactor-cycles')->group(function(){
    Route::get('avail', 'getAvailable')->name('getAvailableReactorCycle');
    Route::middleware('admin')->group(function(){
        Route::get('', 'index');
        Route::get('archived', 'getArchived');
        Route::post('', 'store');
        Route::get('{id}', 'show');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::patch('{id}/status', 'updateStatus');
    });
});

Route::middleware('auth:api')->controller(OrderController::class)->prefix('orders')->group(function() {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::middleware('admin')->group(function() {
        Route::patch('/bulk', 'updateStatusBulk');
        Route::patch('{id}', 'updateStatus');
    });
    Route::get('download', 'downloadList');
    Route::get('{id}', 'show');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('check-change-password', 'checkChangePasswordToken');
    Route::post('change-password', 'changePassword');
    Route::middleware('auth:api')->post('logout', 'logout');
});

Route::middleware('auth:api')->controller(CalendarController::class)->group(function() {
    Route::get('reactors', 'getReactorList');
    Route::post('calendar', 'getReactorAvail');
});

Route::middleware('auth:api')->controller(NotificationController::class)->prefix('notifications')->group(function() {
    Route::get('', 'index');
    Route::patch('', 'updateSeen');
});

Route::middleware('auth:api')->controller(UserDetailsController::class)->prefix('user-details')->group(function() {
    Route::get('', 'show');
    Route::put('', 'update');
});

Route::middleware('auth:api')->controller(LocationAPIController::class)->group(function () {
    Route::get('get-locations', 'getLocations');
});

//for tests
Route::middleware(['auth:api'])->group(function () {
    Route::get('testroute',function(){
    })->name('testroute');
});
