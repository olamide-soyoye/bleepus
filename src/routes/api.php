<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;


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

Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
    //Test endpoint to see how to pass token
    Route::post('auth/refresh',
        [
            Api\AuthController::class, 
            'refreshToken'
        ]
    )->middleware('throttle:5,1')->name('api.auth.refresh');


    Route::get('jobs/around',
        [
            Api\AuthController::class, 
            'getLocationsAround'
        ]
    )->middleware('throttle:5,1')->name('api.jobs.around');
});

Route::post('auth/register',
    [
        Api\AuthController::class, 
        'register'
    ]
)->middleware('throttle:5,1')->name('api.auth.register');

Route::post('auth/login',
    [
        Api\AuthController::class, 
        'login'
    ]
)->middleware('throttle:5,1')->name('api.auth.login');

Route::post('auth/logout',
    [
        Api\AuthController::class, 
        'logout'
    ]
)->name('api.auth.logout');

Route::get('/swagger', function () {
    return view('swagger.index');
});
