<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\User; //user model
use App\UserLocation; //user location model
use App\CompletedMovement; //completed movement model
use App\UserBreak; //user breaks model
use App\Jobs\Job;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:test {user}';
    //protected $signature = 'notification:test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Test Push Notification';

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
        
        echo "Looking for user ". $user_id;
        $user = User::find($user_id);

            //get uuid of user 
            $uuid = $user->uuid;
            echo "uuid ".$uuid . "\n"; 
            
            //***CHANGE TO UPDATED AT
            //get users start and end time matching that UUID order by desc to get last value
            $startTime = UserBreak::where('uuid', $uuid)->value('start_time');
            $endTime = UserBreak::where('uuid', $uuid)->value('end_time');          
            echo "start time " . $startTime . " end time " . $endTime . "\n";

            //time now
            $currentTime = Carbon::now();
            $carbonCurrent = Carbon::createFromFormat('Y-m-d H:i:s', $currentTime);
            $time = $currentTime->toTimeString();
            echo "current time " . $time . "\n";
               
            //create the start/end in carbon objects
            $carbonStart = Carbon::createFromFormat('Y-m-d H:i:s', $startTime);
            $carbonEnd = Carbon::createFromFormat('Y-m-d H:i:s', $endTime);
            //create just the time not date 
            $start = $carbonStart->toTimeString();
            $end = $carbonEnd->toTimeString();
            echo "start time as string " . $start . " end time as string " . $end . "\n";
            //convert back to carbon
            $convertedStart = Carbon::createFromTimeStamp(strtotime($start));
            $convertedEnd = Carbon::createFromTimeStamp(strtotime($end));
            echo "carbon start time " . $convertedStart . " carbon end time " . $convertedEnd . "\n";


            //if current time is between start and end
            echo ($currentTime->between($convertedStart, $convertedEnd)); 
        
//            if ($currentTime->between($convertedStart, $convertedEnd))
//            {
                 
                $this->info('between start and end time');

                //if completed goal is less than break_goal
                $completedBreaks = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('completed_breaks');
                echo "completed breaks: " . $completedBreaks . "\n";
                
                $breakGoal = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('break_goal');
                echo "break goal: " . $breakGoal . "\n";
                
                    //check if user still has breaks left under goal number
//                    if($completedBreaks < $breakGoal) 
//                    {
                        $this->info('Completed break < break goal');

                        //get user dev token
                        $devKey = $user->device_token;
                    //send notification at interval from settings
//                    $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device('3a3ad21b548f7d8c23d3baa534f7fe41bfdc28101e786b10080e0889fcf6d6bb');
                        //new device key***********
                            $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device($devKey);
                        
                        
                    //with uuid from actual user(simulator device)
//                    $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device($user->uuid);
                    
//                    $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Hello message text working!',array(
//                    'badge' => 1,
//                    //'sound' => 'example.aiff',
//
//                    'actionLocKey' => 'Action button title!',
//                    'locKey' => 'Testing notification system', //seems to be the message text
//                    'locArgs' => array(
//                    'localized test arg',
//                    'localized args',
//                    ),
//                    //'launchImage' => 'image.jpg',
//
//                        'custom' => array('custom data' => array(
//                            'we' => 'want', 'send to app'
//                        ))
//                    ));
        
                        $collection = \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameIOS')
                            ->to($devices)
                            ->send('Test notification message.');

                        // get response for each device push
                        foreach ($collection->pushManager as $push) {
                            $response = $push->getAdapter()->getResponse();
                        }
        
                        $this->info('Notification Sent to test device');
            
                    //}//end of if for completed breaks < goal breaks
                
                //}//end of if for between time  
            

        $break = UserBreak::where('uuid', '=', $uuid)->first();
        echo "Break interval is: " .$break->reminder_interval ."\n"; 
        
        //get user_id
        $user_id = $user->id; 
        echo "user_id : " . $user_id . "\n"; 
        //create another job after the the above gets queued. this will create a never ending infinite loop. 
        $job = (new \App\Jobs\SendBreakNotification($user_id))->delay($break->reminder_interval);
        $job_id = dispatch($job);
        echo "JOB ID IS : " . $job_id . "\n";
                
        //UPDATE USER BREAK with new job_id
        $break->job_id = $job_id;
        //update the new values
        $break->save();
        
    }//end of handle function
}
