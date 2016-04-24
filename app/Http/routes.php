<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();
Route::get('/test/{user_id}/{hour}/{minute}', 'APIController@testTime');
Route::get('/home', 'HomeController@index');
/* 
USER ROUTES
==================================================================
*/
Route::get('/api/users', 'APIController@users');
Route::get('/api/user/{id}', 'APIController@user');
Route::get('/api/userid/{uuid}', 'APIController@findUserByUUID');
// add user from ios
Route::get('/api/user/add/{uuid}', 'APIController@newUser');
Route::get('/api/user/update/{uuid}/{device_token}', 'APIController@addDevKey');

/* 
LOCATION ROUTES
==================================================================
*/
Route::get('/api/locations', 'APIController@getAllLocations');
Route::get('/api/location/{uuid}', 'APIController@locationUUID');
// adding new location
Route::get('/api/location/{user_id}/{uuid}/{latitude}/{longitude}/{name}/{unique_id}', 'APIController@newLocation');

// deleting location NOT WORKING***************
Route::get('/api/location/delete/{unique_id}', 'APIController@deleteLocation'); 
//checking if location exists for monitoring
Route::get('/api/check_location/{uuid}/{unique_id}', 'APIController@checkLocation');

/* 
MOVEMENT ROUTES for movement list TODO****************
==================================================================
*/
Route::get('/api/movements', 'APIController@getAllMovements'); //DONE
Route::get('/api/movements/{id}', 'APIController@movementID');//done
// adding new movement ******TODO
//Route::get('/api/location/{uuid}/{latitude}/{longitude}/{name}/{unique_id}', 'APIController@newLocation');

/* 
COMPLETED MOVEMENT ROUTES 
==================================================================
*/
Route::get('/api/completed_movements', 'APIController@getAllCompletedMovements');
Route::get('/api/completed_movements/{uuid}', 'APIController@completedMovementUUID');
// adding new completed movement
//****** HOW TO ADD USER_ID FROM APP??
Route::get('/api/completed_movement/{user_id}/{uuid}/{exercise}/{completed_breaks}/{break_goal}', 'APIController@addCompletedMovement');

/* 
COMPLETED BREAKS ROUTES TODO****************
==================================================================
*/
Route::get('/api/completed_breaks', 'APIController@getBreakSettings');
Route::get('/api/completed_breaks/{uuid}', 'APIController@breakWithUUID');
// update break settings
Route::get('/api/completed_break/{user_id}/{uuid}/{reminder_interval}/{break_goal}/{timezone}/{start_time}/{end_time}', 'APIController@updateBreakSettings');
// update break settings
Route::get('/api/default_break/{user_id}/{uuid}', 'APIController@defaultBreakSettings');

/* 
IOS NOTIFICATION ROUTES
==================================================================
*/
//route for ios notification test
Route::get('/api/test/notification', 'APIController@notification');
//route for location based pushes
Route::get('api/enter/{uuid}', 'APIController@enteredMonitoredLocation');
//delete job_id in queue 
Route::get('api/exit/{uuid}', 'APIController@exitedMonitoredLocation');
//inside geofence, check if jobs exit already
Route::get('api/current/{uuid}', 'APIController@monitorCurrentLocation');