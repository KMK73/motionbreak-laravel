<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\User; //user model
use App\UserLocation; //user location model
use App\CompletedMovement; //completed movement model
use App\UserBreak; //user breaks model
use App\Notification; //user breaks model
use App\Jobs\Job;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:ios {user}';
    //protected $signature = 'notification:test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a mobile Push Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    
    
    public function __construct()
    {
        //pass in the user that launched this job through the controller
//        $this->user = $user; 
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //To retrieve the value of an argument
        $user_id = $this->argument('user');
        
        echo "Looking for user ". $user_id."\n";
        $user = User::find($user_id);
        
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
                $this->info('Completed break < break goal');
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
                    $this->info('carbonEnd < carbonStart add day');
                    echo "carbonEnd < carbonStart new carbonEnd: " . $carbonEnd . "\n";
                }

                // when in production, replace carbonRequest with tzNow.
                if ($tzNow->between($carbonStart, $carbonEnd))
                {               
                    $this->info('between start and end time');
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

                     $this->info('Notification Sent to test device');

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
        //$notification = new Notification;
//        $notification->user_id = $user_id;
//        $notification->job_id = $job_id; 
//        echo "JOB ID saved in notification : " . $job_id . " notification ".$notification. "\n";
//        $notification->save();
        
    //UPDATE USER BREAK with new job_id
    $break->job_id = $job_id;
    //update the new values
    $break->save();

        
    }//end of handle function
}
