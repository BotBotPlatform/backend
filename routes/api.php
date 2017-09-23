<?php

use Illuminate\Http\Request;

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


Route::group(['prefix' => 'user'], function() {
	Route::post('register', 'AuthController@register');
	Route::post('auth', 'AuthController@authenticate');
	Route::post('reset/store', 'AuthController@performPasswordReset');
	Route::post('reset', 'AuthController@sendPasswordReset');
});

Route::group(['prefix' => 'bot', 'middleware' => 'jwt.auth'], function() {
	Route::get('', 'BotController@getBot');
	Route::post('', 'BotController@createBot');
	Route::delete('', 'BotController@deleteBot');
});
