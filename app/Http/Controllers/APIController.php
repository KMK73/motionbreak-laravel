<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB; //to be able to access database for jobs table
use Carbon\Carbon;
use App\Http\Requests;
use App\User; //user model
use App\UserLocation; //user location model
use App\Movement; //movement model
use App\CompletedMovement; //completed movement model
use App\UserBreak; //user breaks model
use App\Jobs\SendBreakNotification;
use Davibennun\LaravelPushNotification;//package to send ios notifications

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
        //return $user->id;
        return response()->json($user);
    }
    
    //create new user from ios 
    public function newUser($uuid) {
        $user = new User;
        $user->uuid = $uuid; 
        $user->save();
                
        //set break settings to default when user is created ****TODO
//        $break = new UserBreak;
//        $break = UserBreak::where('uuid', '=', $uuid)->first();
//        $break->uuid = $uuid;
//        $break->reminder_interval = 3600;//1 hour
//        $break->break_goal = 10;
//        //update the new values
//        $break->save();
        
        return response()->json($user); 
    }
    
   //update user to add device token
    public function addDevKey($uuid, $device_token) {
        
        //find user break row
        $user = User::where('uuid', '=', $uuid)->first();
        $user->device_token = $device_token;

        //update the new values
        $user->save();
                
        return response()->json($user); 
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
        $locations = UserLocation::where('uuid', '=', $uuid)->get();
        //added key for obj c to pull
        return response()->json(array('locations' => $locations));
    }
    
    //finding location for monitored region check
    public function checkLocation ($uuid, $unique_id) {
        $locations = UserLocation::where('uuid', '=', $uuid)->get(); 
         
        foreach ($locations as $location) {
          //  echo $location->unique_id . "\n";
            if($location->unique_id == $unique_id){
                //echo "location->unique_id = " . $location->unique_id . "\n";
                    return response()->json(['success' => true]);
            }
        }
        return response()->json(['failure'=>true]);
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
    public function completedMovementUUID ($uuid){
        //get movements for today and uuid
       $movements = CompletedMovement::where('uuid', '=', $uuid)
                    ->whereDate('created_at', '=', date('Y-m-d'))
                    ->get();
        
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
            $table->integer('job_id');
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

    
    //set default values 
    public function defaultBreakSettings($user_id, $uuid,$reminder_interval, $break_goal, $start_time, $end_time) {
        
        //new user break row
        $break = new UserBreak;
        
        $break->user_id = $user_id;
        $break->uuid = $uuid;
        $break->reminder_interval = $reminder_interval;
        $break->break_goal = $break_goal;
        $break->start_time = $start_time;
        $break->end_time = $end_time;
        //update the new values
        $break->save();
                
        return response()->json($break); 
    }
    
   //update only one row 
    public function updateBreakSettings($user_id, $uuid,$reminder_interval, $break_goal,$timezone, $start_time, $end_time) {
        
        //find user break row
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        
        $break->user_id = $user_id;
        $break->uuid = $uuid;
        $break->reminder_interval = $reminder_interval;
        $break->timezone = $timezone;
        $break->break_goal = $break_goal;
        $break->start_time = $start_time;
        $break->end_time = $end_time;
        //update the new values
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
        
        //delete any job that may still be in the queue
        //FIND USER BREAK RECORD
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        //GET JOB ID FROM USER BREAK RECORD
        $job_id = $break->job_id;
        echo "Break exiting JOB ID IS : " . $job_id . "\n"; 
        //DELETE FROM JOBS TABLE WHERE ID = JOB_ID
        //DB is database call directly
        DB::delete('delete from jobs where id = :id', ['id' => $job_id]);   
        
        //get user_id
        $user_id = $user->id; 
        //echo "user_id : " . $user_id . "\n"; 
 
        //sending first notification after a delay of interval time
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        //with reminder interval and hard coded 1 for user******
        $job = (new SendBreakNotification($user_id))->delay($break->reminder_interval);
        //dispatch job
        $job_id = $this->dispatch($job);
        
        //UPDATE USER BREAK with job_id
        $break->job_id = $job_id;
        //echo "Break starting JOB ID IS : " . $break->job_id . "\n"; 
        $break->save();
//        return response()->json(['success' => true]);
       return response()->json($job_id);

    }
    
    public function exitedMonitoredLocation($uuid) {
        //get user 
        $user = User::where('uuid', '=', $uuid)->first();
        
        //FIND USER BREAK RECORD
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        //GET JOB ID FROM USER BREAK RECORD
        $job_id = $break->job_id;
        echo "Break exiting JOB ID IS : " . $job_id . "\n"; 

        //DELETE FROM JOBS TABLE WHERE ID = JOB_ID
        //DB is database call directly
        DB::delete('delete from jobs where id = :id', ['id' => $job_id]);        
        
        return response()->json(['success' => true]);
    }
    
    public function monitorCurrentLocation($uuid) {
        //using device token to look up user
        $user = User::where('uuid', '=', $uuid)->first();
        //get user_id
        $user_id = $user->id; 

        //check DB for any running jobs 
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        //GET JOB ID FROM USER BREAK RECORD
        $job_id = $break->job_id;
        echo "Current Location: JOB ID IS : " . $job_id . "\n"; 

        //DELETE FROM JOBS TABLE WHERE ID = JOB_ID
        //DB is database call directly
        DB::delete('delete from jobs where id = :id', ['id' => $job_id]);        
        
        //sending first notification after a delay of interval time
        $break = UserBreak::where('uuid', '=', $uuid)->first();
        //with reminder interval and hard coded 1 for user******
        $job = (new SendBreakNotification($user_id))->delay($break->reminder_interval);
        //dispatch job
        $job_id = $this->dispatch($job);
        
        //UPDATE USER BREAK with job_id
        $break->job_id = $job_id;
        //echo "Break starting JOB ID IS : " . $break->job_id . "\n"; 
        $break->save();
//        return response()->json(['success' => true]);
       return response()->json($job_id);

    }
    
    //testing notfication purely from URL in browser
    public function notification() {
        
        $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device('f3a0aac8edee125b9973e3b6c491258cfc669ca7d1a75ac6b55e2dd7f79ce0ac');
        
        $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Hello message text working!',array(
            //'badge' => 1,
            //'sound' => 'example.aiff',

            'actionLocKey' => 'to take a Motion Break!',
            'locKey' => 'Time to get fit. Take a motion break!', //seems to be the message text
            'locArgs' => array(
                'Testing action arg 1',
                'Testing action arg 2',
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
    
    public function testTime($user_id, $hour, $minute) {
        $data = array();
        $user = User::find($user_id);

        //get uuid of user 
        $uuid = $user->uuid;
        $data["user"] = $uuid;
        $completedBreaks = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('completed_breaks');
        $data["breaks"] = $completedBreaks;
        $breakGoal = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('break_goal');
        $data["goal"] = $breakGoal;
                
        //check if user still has breaks left under goal number
        if($completedBreaks < $breakGoal) 
        {
            $data["hasBreaks"] = true;
            //get users start and end time matching that UUID order by desc to get last value
            $startTime = UserBreak::whereUuid($uuid)->value('start_time');
            $data["start"] = $startTime;
            $endTime = UserBreak::whereUuid($uuid)->value('end_time');          
            $data["end"] = $endTime;
            
            $tzDiff = UserBreak::whereUuid($uuid)->value('timezone');
            $d = abs($tzDiff);
            $s = $tzDiff < 0 ? '-' : '+';
            // we need to add the signs for the timezone to be parsed.
            $tzName = Carbon::now("{$s}{$d}")->tzName;
            // this gives us the current datetime in the users timezone.
            $tzNow = Carbon::now($tzName);
            $data["now_carbon"] = $tzNow;

            $carbonStart = Carbon::createFromFormat('H:i:s', $startTime, $tzName);
            $carbonStart->modify("{$s}{$d} hours");
            $data["start_carbon"] = $carbonStart;
            
            $carbonEnd = Carbon::createFromFormat('H:i:s', $endTime, $tzName);
            $carbonEnd->modify("{$s}{$d} hours");            
            if ($carbonEnd->lte($carbonStart)) {
                $carbonEnd->addDay();
            }
            $data["end_carbon"] = $carbonEnd;
            
            $carbonRequest = Carbon::createFromFormat('H:i:s', "$hour:$minute:00", $tzName);
            $data["request_carbon"] = $carbonRequest;
            // when in production, replace carbonRequest with tzNow.
            if ($carbonRequest->between($carbonStart, $carbonEnd))
            {               
                $data["is_between"] = true;
            }
            else {
                $data["is_between"] = false;
            }
            //create carbon objects for start, end, current WITH OFFSET OF USERS TIMEZONE 
//            $timezoneDiff = UserBreak::where('uuid', $uuid)->value('timezone');
//            $data["tz"] = $timezoneDiff;
//            // we need to add the signs for the timezone to be parsed.
//            $d = abs($timezoneDiff);
//            $s = $timezoneDiff < 0 ? '-' : '+';
//            $timezoneName = Carbon::now("{$s}{$d}")->tzName;
//            $carbonStart = Carbon::createFromFormat('H:i:s', $startTime, $timezoneName);
//            $carbonStart_ = Carbon::createFromFormat('H:i:s', $startTime);
//            $carbonStart_->tz($timezoneName);
//            
//            $data["start_carbon"] = $carbonStart;
//            $carbonEnd = Carbon::createFromFormat('H:i:s', $endTime);
//            $carbonEnd->tz($timezoneName);
//            $data["end_carbon"] = $carbonEnd;
            
//            //***if time is 00:00 to 12:00 for start time make it previous day
//            $minStart = Carbon::createFromTime(0, 0, 0); 
//            $maxStart = Carbon::createFromTime(12, 0, 0);
//            if ($carbonStart->between($minStart,$maxStart)){
//                $carbonStart->subDay();
//                $data["shift_start"] = true;
//                $data["shifted_start"] = $carbonStart;
//            }
//            else {
//                $data["shift_start"] = false;
//            }
            
//            //if End time is less than start time then add a day
//            if ($carbonEnd->lte($carbonStart)){
//                $carbonEnd->addDay();
//                $data["shift_end"] = true;
//                $data["shifted_time"] = $carbonEnd;
//            }
//            else {
//                $data["shift_end"] = false;
//            }
//            //time now
////            $currentTime = Carbon::now();
//            $currentTime = Carbon::createFromTime($hour, $minute, 0);
//            $data["current_time"] = $currentTime;
//            
//            if ($currentTime->between($carbonStart, $carbonEnd))
//            {               
//                $data["is_between"] = true;
//            }
//            else {
//                $data["is_between"] = false;
//            }
        }
        else {
            $data["hasBreaks"] = false;
        }
        return response()->json($data); 
    }
}