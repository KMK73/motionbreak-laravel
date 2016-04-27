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
use App\Notification; //notifications model
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
    
    public function newLocation($user_id, $uuid,$latitude, $longitude, $name, $unique_id) {
        $userLocation = new UserLocation;
        $userLocation->user_id = $user_id;
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
    
    public function newMovement() {
        
        $userMovement = new Movement; 
                
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
        //get the timezone difference int passed from user
        $tzDiff = UserBreak::whereUuid($uuid)->value('timezone');
        $d = abs($tzDiff);
        $s = $tzDiff < 0 ? '-' : '+';
               
        // need to add the signs for the timezone to be parsed.
        // variable to get the timezone name
        $tzName = Carbon::now("{$s}{$d}")->tzName;
        $tzNow = Carbon::now($tzName);
        //echo "tzNow: " . $tzNow . "\n";
        $tzNow->format('Y-m-d'); // Equivalent: echo $dt->format('Y-m-d');
        $nowDate = $tzNow->toDateString(). "\n";  
        //echo "tzNow just date: " . $nowDate . "\n";

        $movements = CompletedMovement::where('uuid', '=', $uuid)
                    ->whereDate('created_at', '=', $nowDate)
                    ->get();
        
        return response()->json(array('movements' => $movements));
    }
    
    public function addCompletedMovement($user_id, $uuid,$exercise, $completed_breaks, $break_goal) {
        //add 1 to completed breaks in user_breaks table
        $break = UserBreak::where('user_id', '=', $user_id)->first();
        $break->completed_movement = $completed_breaks;
        $break->save();
        
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
COMPLETED BREAKS ROUTES 
==================================================================
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
    public function defaultBreakSettings($user_id, $uuid) {
        
        //new user break row
        $break = new UserBreak;
        $break->completed_movement = 0;
        $break->user_id = $user_id;
        $break->uuid = $uuid;
        $break->reminder_interval = 3600;
        $break->break_goal = 10;
        $break->start_time = "13:00:00";
        $break->end_time = "18:00:00";
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

//        //delete any job that may still be in the queue
//        //FIND USER BREAK RECORD
//        $break = UserBreak::where('uuid', '=', $uuid)->first();
//        //GET JOB ID FROM USER BREAK RECORD
//        $job_id = $break->job_id;
//        echo "Break exiting JOB ID IS : " . $job_id . "\n"; 
//        //DELETE FROM JOBS TABLE WHERE ID = JOB_ID
//        //DB is database call directly
//        DB::delete('delete from jobs where id = :id', ['id' => $job_id]);   
        
        //get user_id
        $user_id = $user->id; 
        //echo "user_id : " . $user_id . "\n"; 
 
//         //check if notifications table has multiple jobs and delete those job ids from jobs table 
//        $lastJobID = Notification::where('user_id', '=', $user_id)->last()->value('job_id');
//        $notifications = Notification::where('user_id', '=', $user_id)->value('job_id')->get();
//        
//        echo "notifications last job id: " . $lastJobID . "\n"; 
//        echo "notifications: " . $notifications . "\n"; 
//        DB::table('notifications')->where('user_id', '=', $user_id)
//                        ->where('job_id', '!=',$lastJobID);
//
//        //if there is more than 1 notification listed for a user delete them
//        //DB is database call directly
//        foreach ($notifications as $notification) {
//            DB::delete('delete from jobs where id = :id', ['id' => $notification]); 
//            echo "deleted job: " . $notification . "\n"; 
//
//        }
        
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
    public function notificationJobTable($user_id) {
        
    //To retrieve the value of an argument
//    $user_id = $this->argument('user');

    echo "Looking for user ". $user_id."\n";
    $user = User::find($user_id);
         
    //check if notifications table has multiple jobs and delete those job ids from jobs table 
        $notfications = Notification::where('user_id', '=', $user_id);
        $jobID = $notfications->value('job_id');
//        $notifications = Notification::where('user_id', '=', $user_id)->value('job_id')->get();
        
        echo "notifications last job id: " . $jobID . "\n"; 
        echo "notifications: " . $notifications . "\n"; 
        DB::table('jobs')->where('job_id', '!=',$jobID);

        //if there is more than 1 notification listed for a user delete them
        //DB is database call directly
        foreach ($notifications as $notification) {
            DB::delete('delete from jobs where id = :id', ['id' => $notification]); 
            echo "deleted job: " . $notification . "\n"; 

        }
        
    //get uuid of user 
    $uuid = $user->uuid;
    echo "uuid ".$uuid . "\n"; 

    //if completed goal is less than break_goal
    $completedBreaks = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('completed_breaks');
    echo "completed breaks: " . $completedBreaks . "\n";

    $breakGoal = UserBreak::whereUuid($uuid)->value('break_goal');
    echo "break goal: " . $breakGoal . "\n";

        //check if user still has breaks left under goal number
        if($completedBreaks < $breakGoal) 
        {
            //get users start and end time matching that UUID order by desc to get last value
            $startTime = UserBreak::whereUuid($uuid)->value('start_time');
            echo "db start time: " . $startTime . "\n";

            $endTime = UserBreak::whereUuid($uuid)->value('end_time');
            echo "db end time: " . $endTime . "\n";

            //get the timezone difference int passed from user
            $tzDiff = UserBreak::whereUuid($uuid)->value('timezone');
            $d = abs($tzDiff);
            $s = $tzDiff < 0 ? '-' : '+';

            // need to add the signs for the timezone to be parsed.
            // variable to get the timezone name
            $tzName = Carbon::now("{$s}{$d}")->tzName;
            echo "tzName: " . $tzName . "\n";

            // this gives the current datetime in the users timezone.
            $tzNow = Carbon::now($tzName);
            echo "tzNow: " . $tzNow . "\n";

            //create start and end times with modified times with users timezone.
            $carbonStart = Carbon::createFromFormat('H:i:s', $startTime, $tzName);
            $carbonStart->modify("{$s}{$d} hours");
            echo "modified carbonStart: " . $carbonStart . "\n";

            $carbonEnd = Carbon::createFromFormat('H:i:s', $endTime, $tzName);
            $carbonEnd->modify("{$s}{$d} hours");  
            echo "modified carbonEnd: " . $carbonEnd . "\n";

            //if carbonEnd is < carbonStart the day needs to be pushed forward
            if ($carbonEnd->lte($carbonStart)) {
                $carbonEnd->addDay();
                //$this->info('carbonEnd < carbonStart add day');
                echo "carbonEnd < carbonStart new carbonEnd: " . $carbonEnd . "\n";
            }

            if ($tzNow->between($carbonStart, $carbonEnd))
            {               
                //$this->info('between start and end time');
                echo "BETWEEN start= " . $carbonStart . " now= ".$tzNow." end= ".$carbonEnd."\n";

                //get user dev token
                $devKey = $user->device_token;
                //send notification at interval from settings
                //new device key***********
                 $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device($devKey);

                $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Hello message text working!',array(
                //'badge' => 1,
                //'sound' => 'example.aiff',

                'actionLocKey' => 'take a Motion Break.',
                'locKey' => 'Time to get fit, take a Motion Break!', //seems to be the message text
                //'locArgs' => array(
                //'localized test arg',
                //'localized args',
                //),
                //'launchImage' => 'image.jpg',

//                        'custom' => array('custom data' => array(
//                            'we' => 'want', 'send to app'
//                        ))
                ));

                    $collection = \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameIOS')
                        ->to($devices)
                        ->send($message);

                    // get response for each device push
                    foreach ($collection->pushManager as $push) {
                        $response = $push->getAdapter()->getResponse();
                    }

                 //$this->info('Notification Sent to test device');

            }//end of if for between time

        }//end of if for completed breaks < goal breaks
            
    //get the user break to retreive the reminder interval
    $break = UserBreak::where('uuid', '=', $uuid)->first();
    echo "Break interval is: " .$break->reminder_interval ."\n"; 

    //get user_id
    $user_id = $user->id; 
    echo "user_id : " . $user_id . "\n"; 
    //create another job after the the above gets queued. this will create a never ending infinite loop. 
    $job = (new \App\Jobs\SendBreakNotification($user_id))->delay($break->reminder_interval);
    $job_id = dispatch($job);
    echo "JOB ID IS : " . $job_id . "\n";

        //prevent multiple deleting least recent entries 
        //new notification to table
        $notification = new Notification;
        $notification->user_id = $user_id;
        $notification->job_id = $job_id; 
        echo "JOB ID saved in notification : " . $job_id . " notification ".$notification. "\n";
        $notification->save();
        
    //UPDATE USER BREAK with new job_id
    $break->job_id = $job_id;
    //update the new values
    $break->save();
    
}   
    
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
    
    
    public function testJobsTable($user_id,) {
         
        echo "Looking for user ". $user_id."\n";
        $user = User::find($user_id);
         
        //check if notifications table has multiple jobs and delete those job ids from jobs table 
        $notfications = Notification::where('user_id', '=', $user_id);
        $jobID = $notfications->value('job_id');
//        $notifications = Notification::where('user_id', '=', $user_id)->value('job_id')->get();
        
        echo "notifications last job id: " . $jobID . "\n"; 
        echo "notifications: " . $notifications . "\n"; 
//        DB::table('jobs')->where('job_id', '!=',$jobID);
//
//        //if there is more than 1 notification listed for a user delete them
//        //DB is database call directly
//        foreach ($notifications as $notification) {
//            DB::delete('delete from jobs where id = :id', ['id' => $notification]); 
//            echo "deleted job: " . $notification . "\n"; 
//
//        }
    }
}

    