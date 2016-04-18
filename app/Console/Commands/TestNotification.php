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
        
        echo "Looking for user ". $user_id."\n";
        $user = User::find($user_id);

        //get uuid of user 
        $uuid = $user->uuid;
        echo "uuid ".$uuid . "\n"; 
            
        //if completed goal is less than break_goal
        $completedBreaks = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('completed_breaks');
        echo "completed breaks: " . $completedBreaks . "\n";
                
        $breakGoal = CompletedMovement::orderby('created_at', 'desc')->where('uuid', $uuid)->value('break_goal');
        echo "break goal: " . $breakGoal . "\n";
                
        //check if user still has breaks left under goal number
        if($completedBreaks < $breakGoal) 
        {
            $this->info('Completed break < break goal');
            //***CHANGE TO UPDATED AT
            //get users start and end time matching that UUID order by desc to get last value
            $startTime = UserBreak::where('uuid', $uuid)->value('start_time');
            $endTime = UserBreak::where('uuid', $uuid)->value('end_time');          
            echo "start time " . $startTime . " end time " . $endTime . "\n";
//        $result = $date->format('Y-m-d H:i:s');

//            //create just the time not date 
//            $carbonStart = $startTime->toTimeString();
//            $carbonEnd = $endTime->toTimeString();
//            echo "start time as string " . $carbonStart . " end time as string " . $carbonEnd . "\n";
//       
            //check if end is < than start (day is day before etc), if it is then push one day farther for endTime
            //create the start/end in carbon objects
            $carbonStart = Carbon::createFromFormat('Y-m-d H:i:s', $startTime);
            $carbonEnd = Carbon::createFromFormat('Y-m-d H:i:s', $endTime);
            echo "carbon start time " . $carbonStart . " carbon end time " . $carbonEnd . "\n";
            
            $diff = $carbonStart->diffInDays($carbonEnd);
            echo "difference ". $diff. "\n";       

            if ($carbonStart > $carbonEnd)
            {
                echo "carbonStart is greater than carbonEnd in days : ". $carbonStart ." end ". $carbonEnd ."\n";
                //add a day to the endTime
                $carbonEnd->addDay();      
                echo "new carbonEnd ". $carbonEnd ."\n";
            }
        
            //time now
            $currentTime = Carbon::now();
            echo "carbon now ". $currentTime. "\n";
        
//            //check if startTime DAY is < carbonNow day, if it is then push one day farther for startTime
//            if ($carbonStart < $currentTime)
//            {
//                $diff = $carbonStart->diffInDays($currentTime);
//                echo "difference ". $diff. "\n";  
//                echo "carbonStart is less than current time". $carbonStart ." end ". $currentTime ."\n";
//                //add a day to the endTime
//                $carbonStart->addDay();      
//                echo "new carbonEnd ". $carbonEnd ."\n";
//            }
            //$carbonCurrent = Carbon::createFromFormat('Y-m-d H:i:s', $currentTime);
//            $time = $currentTime->toTimeString();
//            echo "current time " . $time . "\n";
//            $currentTime = Carbon::createFromTimeStamp(strtotime($time));
//            echo "current time ".$currentTime . "\n";
    


//            //convert back to carbon
//            $convertedStart = Carbon::createFromTimeStamp(strtotime($start));
//            $convertedEnd = Carbon::createFromTimeStamp(strtotime($end));
//            echo "carbon start time " . $convertedStart . " carbon end time " . $convertedEnd . "\n";

 
            //if current time is between start and end
            echo "between bool ".($currentTime->between($carbonStart, $carbonEnd))."\n"; 
        
            if ($currentTime->between($carbonStart, $carbonEnd))
            {
                 
                $this->info('between start and end time');


                //get user dev token
                $devKey = $user->device_token;
                //send notification at interval from settings
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
            
                }//end of if for between time
                
    }//end of if for completed breaks < goal breaks
            

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
