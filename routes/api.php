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
	Route::group(['middleware' => 'jwt.auth'], function() {
		Route::get('token', 'AuthController@checkFacebookToken');
		Route::post('token', 'AuthController@addFacebookToken');
	});
});

Route::group(['prefix' => 'bot', 'middleware' => 'jwt.auth'], function() {
	Route::get('', 'BotController@getBot');
	Route::post('', 'BotController@createBot');
	Route::delete('', 'BotController@deleteBot');
	Route::post('spinUp', 'BotController@spinUpBot');
	Route::post('shutDown', 'BotController@shutDownBot');
	Route::post('reloadBot', 'BotController@reloadBot');
	Route::get('admin', 'BotController@getBotData');
	Route::get('admin/{bot_uuid}/outputlogs', 'BotController@getBotOutputLog');
	Route::get('admin/{bot_uuid}/errorlogs', 'BotController@getBotErrorLog');
	Route::post('/toggleFeature', 'BotController@toggleBotFeatures');

});

//Feedback endpoints
Route::group(['prefix' => 'feedback'], function() {
	//Public Endpoints
	Route::post('', 'FeedbackController@createFeedback');

	//Authenticated Endpoints
	Route::group(['middleware' => 'jwt.auth'], function() {
		Route::get('', 'FeedbackController@getFeedback');
		Route::get('category', 'FeedbackController@getFeedbackCategories');
		Route::post('category', 'FeedbackController@createFeedbackCategory');
		Route::post('category/delete', 'FeedbackController@deleteFeedbackCategory');
	});
});

//Appointment endpoints
Route::group(['prefix' => 'appointment'], function() {
	//Public Endpoints
	Route::get('/calendar/{uuid}', 'AppointmentController@generateCalendarForBot');
	//Authenticated Endpoints
	Route::group(['middleware' => 'jwt.auth'], function() {
		Route::post('', 'AppointmentController@createAppointment');
		Route::get('', 'AppointmentController@getAppointments');
		Route::get('/hours', 'AppointmentController@getBusinessHours');
		Route::post('/hours', 'AppointmentController@setBusinessHours');
	});
});

//Support Ticket endpoints
Route::group(['prefix' => 'tickets'], function() {
	//Authenticated Endpoints
	Route::group(['middleware' => 'jwt.auth'], function() {
		Route::get('', 'SupportController@getSupportTickets');
		Route::post('', 'SupportController@createSupportTicket');
		Route::post('/{ticket_id}/resolve', 'SupportController@resolveSupportTicket');
	});
});

//Shop endpoints
Route::group(['prefix' => 'shop'], function() {
	//Authenticated Endpoints
	Route::group(['middleware' => 'jwt.auth'], function() {
		Route::get('getItems', 'ShopController@getShop');
		Route::post('set', 'ShopController@setShopUrl');
		Route::get('name', 'ShopController@getShopName');
	});
});

//Forwarding for bots
Route::group(['prefix' => 'facebook'], function() {
	Route::get('{uuid}', 'BotController@authenticateBot');
	Route::post('{uuid}', 'BotController@forwardBotMessage');
});
