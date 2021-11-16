<?php

use Illuminate\Http\Request;
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth; 

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
    'prefix' => 'auth'
], function () {
    Route::post('check', 'AuthController@checkExistingUser');
    Route::post('sendmail', 'AuthController@sendMail');
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signUp');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::post('logout', 'AuthController@logout');
    Route::get('/saluda', function () {
        return response()->json(['saludo' => 'hola']);
     });
});