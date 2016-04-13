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

//Route::get('user/{id}', function ($id) {
//    return 'User '.$id;
//});

Route::auth();

Route::get('/home', 'HomeController@index');
Route::get('/api/users', 'APIController@users');
Route::get('/api/user/{id}', 'APIController@user');
Route::get('/api/user/{uuid}', 'APIController@findUserByUUID');

/* 
LOCATION ROUTES
==================================================================
*/
Route::get('/api/locations', 'APIController@getAllLocations');
Route::get('/api/location/{uuid}', 'APIController@locationUUID');
// adding new location
Route::get('/api/location/{uuid}/{latitude}/{longitude}/{name}/{unique_id}', 'APIController@newLocation');

// deleting location NOT WORKING***************
Route::get('/api/location/delete/{unique_id}', 'APIController@deleteLocation'); 


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
        Schema::create('user_breaks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('break_goal');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('reminder_interval');
            $table->integer('user_id');
            $table->string('uuid');
            $table->timestamps();
        });
*/
Route::get('/api/completed_breaks', 'APIController@getBreakSettings');
Route::get('/api/completed_breaks/{uuid}', 'APIController@breakWithUUID');
// update break settings
Route::get('/api/completed_break/{user_id}/{uuid}/{reminder_interval}/{break_goal}/{start_time}/{end_time}', 'APIController@updateBreakSettings');

/* 
IOS NOTIFICATION ROUTES
==================================================================
*/
//route for ios notification test
Route::get('/api/test/notification', 'APIController@notification');
//route for location based pushes
Route::get('api/enter/{uuid}', 'APIController@enteredMonitoredLocation');
Route::get('api/exit/{uuid}', 'APIController@exitedMonitoredLocation');