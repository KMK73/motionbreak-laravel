<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckInsideGeofenceNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:hourly {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test server command every hour.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
         //get user dev token
        $devKey = $user->device_token;
        
        $devices = \Davibennun\LaravelPushNotification\Facades\PushNotification::Device($devKey);
        
        $message = \Davibennun\LaravelPushNotification\Facades\PushNotification::Message('Hello message text working!',array(
            //'badge' => 1,
            //'sound' => 'example.aiff',

            'actionLocKey' => 'to take a Motion Break!',
            'locKey' => 'Every hour laravel notification.', //seems to be the message text
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
        
            $job = (new \App\Jobs\CheckInsideGeofenceNotification($user_id))->delay(1200);
            $job_id = dispatch($job);
            echo "JOB ID IS : " . $job_id . "\n";
    }
    }
}
