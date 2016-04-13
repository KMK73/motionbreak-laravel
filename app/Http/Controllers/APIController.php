<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User; //user model
use App\UserLocation; //user location model
use App\Movement; //movement model
use App\CompletedMovement; //completed movement model
use App\UserBreak; //user breaks model
use App\Jobs\SendBreakNotification;
use Davibennun\LaravelPushNotification;

class APIController extends Controller
{
    public function users() {
        $users = User::all();
        return response()->json($users);
    }
    
    public function user($id) {
        $user = User::find($id);
        return response()->json($user);
    }
    
    public function findUserByUUID($uuid) {
        $user = User::where('uuid', '=', $uuid)->first();
        return $user->id;
    }
    

/* =======================================================================
    LOCATION METHODS: 

newLocation - adds new location to user_locations table
getAllLocations - pulls entire user_locations table
locationUUID - gets all locations for that one user

=======================================================================
*/
    
    public function newLocation($uuid,$latitude, $longitude, $name, $unique_id) {
        
        $userLocation = new UserLocation;
        $userLocation->uuid = $uuid;
        $userLocation->longitude = $longitude;
        $userLocation->latitude = $latitude;
        $userLocation->name = $name;
        $userLocation->unique_id = $unique_id; 
        $userLocation->save();
                
        return response()->json($userLocation); 
    }
    
    //delete location passed with uuid, name, unique_id (unique to location)
    public function deleteLocation($unique_id) {
        
//        $location = UserLocation::find($unique_id);
        $location = UserLocation::where('unique_id', '=', $unique_id)->first();
        

        //delete that location
        $location->delete(); 
        
        return 'Location deleted'; 
    }
    
    
    public function getAllLocations(){
        $locations = UserLocation::all();
        return response()->json(array('locations' => $locations)); //added key for obj c to pull
    }
    
    //testing with uuid WORKING
    public function locationUUID($uuid) {
                    //Model::whereFoo('foo', '=', 'bar')->first();
        $locations = UserLocation::where('uuid', '=', $uuid)->get();
        //added key for obj c to pull
        return response()->json(array('locations' => $locations));
    }

/* =======================================================================
    MOVEMENT METHODS: for movement list NEVER CHANGES JUST ADDITIONS FROM ADMIN

newMovement - adds new movement to movements table
getAllMovements - pulls entire movements table
movementUUID - gets all movements for that one user

=======================================================================
*/
    public function getAllMovements(){
        $movements = Movement::all();
        //added key for obj c to pull
        return response()->json(array('movements' => $movements)); 
    }
    
    // ******TODO
    public function newMovement() {
        
        $userMovement = new Movement; 
//        $userLocation = new UserLocation;
//        $userLocation->uuid = $uuid;
//        $userLocation->longitude = $longitude;
//        $userLocation->latitude = $latitude;
//        $userLocation->name = $name;
//        $userLocation->unique_id = $unique_id; 
//        $userLocation->save();
                
        return response()->json($userMovement); 
    }

    public function movementID($id) {
        $movement = Movement::find($id);
        return response()->json($movement);
    }
/* =======================================================================
    COMPLETED MOVEMENT METHODS: 

newMovement - adds new movement to movements table
getAllMovements - pulls entire movements table
movementUUID - gets all movements for that one user

=======================================================================
*/
    public function getAllCompletedMovements(){
        $movements = CompletedMovement::all();
        //added key for obj c to pull
        return response()->json(array('movements' => $movements)); 
    }
    public function completedMovementUUID($uuid) {
        $movements = CompletedMovement::where('uuid', '=', $uuid)->get();;
        return response()->json(array('movements' => $movements));
    }
    
    public function addCompletedMovement($user_id, $uuid,$exercise, $completed_breaks, $break_goal) {
        
        $newMovement = new CompletedMovement;
        
        $newMovement->user_id = $user_id;
        $newMovement->uuid = $uuid;
        $newMovement->exercise = $exercise;
        $newMovement->completed_breaks = $completed_breaks;
        $newMovement->break_goal = $break_goal; 
        $newMovement->save();
                
        return response()->json($newMovement); 
    }
    
    
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
    public function getBreakSettings(){
        $breaks = UserBreak::all();
        //added key for obj c to pull
        return response()->json(array('breaks' => $breaks)); 
    }
    public function breakWithUUID($uuid) {
        $breaks = UserBreak::where('uuid', '=', $uuid)->get();;
        return response()->json(array('breaks' => $breaks));
    }

   //*********WANT TO MAKE THIS AN UPDATE NOT POST 
    public function addBreakSettings($user_id, $uuid,$reminder_interval, $break_goal, $start_time, $end_time) {
        
        
        $break = UserBreak::where('user_id', '=', $user_id)->first();
        
        
//        $newBreak = new UserBreak;
        
        $break->user_id = $user_id;
        $break->uuid = $uuid;
        $break->reminder_interval = $reminder_interval;
        $break->break_goal = $break_goal;
        $break->start_time = $start_time;
        $break->end_time = $end_time;

        $break->save();
                
        return response()->json($break); 
    }
    
 /* =======================================================================
    IOS NOTIFICATION METHODS: 

=======================================================================
*/   
    public function enteredMonitoredLocation($uuid) {
        //using device token to look up user
        $user = User::where('uuid', '=', $uuid)->first();
//        $user->monitoringLocation = true;
//        $user->save();
        //sending first notification after a delay of interval time
        $break = UserBreak::where('uuid', '=', $uuid)->first();
//        $job = (new SendBreakNotification($user))->delay($break->reminder_interval);
        $job = (new SendBreakNotification(1))->delay(60);
        $job_id = $this->dispatch($job);
        
        //UPDATE USER BREAK with job_id
//        $break->job_id = $job_id;
        return response()->json(['success' => true]);
    }
    
    public function exitedMonitoredLocation($uuid) {
        $user = User::where('uuid', '=', $uuid)->first();
        \Artisan::queue('queue:clear', ['connection' => 'database', 'queue' => 'user'. $user->id]);   
        
        //FIND USER BREAK RECORD
        
        //GET JOB ID FROM USER BREAK RECORD
        
        //DELETE FROM JOBS TABLE WHERE ID = JOB_ID
//         DB::delete('delete from jobs WHERE id = ?', [$job_id]);
//        DB::delete('delete from jobs WHERE id = $job_id');
        
        return response()->json(['success' => true]);
    }
    
    public function notification() {
        
        $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device('3a3ad21b548f7d8c23d3baa534f7fe41bfdc28101e786b10080e0889fcf6d6bb');
        
        $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Hello message text working!',array(
            'badge' => 1,
            //'sound' => 'example.aiff',

            'actionLocKey' => 'Action button title!',
            'locKey' => 'localized key', //seems to be the message text
            'locArgs' => array(
                'localized test arg',
                'localized args',
            ),
            //'launchImage' => 'image.jpg',

            'custom' => array('custom data' => array(
                'we' => 'want', 'send to app'
            ))
        ));
        
    $collection = \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameIOS')
        ->to($devices)
        ->send($message);

        // get response for each device push
        foreach ($collection->pushManager as $push) {
            $response = $push->getAdapter()->getResponse();
        }
    }
//        $device = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device('1b880d91b5d26cfbc5ac1653f233f2a3c98015adca9d3cfc6187e50ec9cc8d04');
//        
//        $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::DeviceCollection(array($device));
//        
//        
//        $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Message Text',array(
//            'badge' => 1,
//            //'sound' => 'example.aiff',
//
//            'actionLocKey' => 'Action button title!',
//            'locKey' => 'localized key',
//            'locArgs' => array(
//                'localized args',
//                'localized args',
//            ),
//            //'launchImage' => 'image.jpg',
//
//            'custom' => array('custom data' => array(
//                'we' => 'want', 'send to app'
//            ))
//        ));
//
//    $collection = \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameIOS')
//        ->to($devices)
//        ->send($message);
//
//        // get response for each device push
//        foreach ($collection->pushManager as $push) {
//            $response = $push->getAdapter()->getResponse();
//        }
//
//    }
    

}