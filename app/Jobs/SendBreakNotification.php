<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use App\User;


class SendBreakNotification extends Job implements ShouldQueue, SelfHandling
{
    use InteractsWithQueue, SerializesModels;

    public $user_id; 
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        //pass in the user that launched this job through the controller
        $this->user_id = $user_id; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $user = $this->user_id;
//         $user = User::find(1);
        //
//        $this->user->break_interval()->reminder_interval;
        
        \Artisan::queue('notification:test', [
            'user' => $this->user_id]);
        //create another job after the the above gets queued. this will create a never ending infinite loop. THIS IS BEING DONE IN TEST NOTIFICATION.PHP

        //$this->user->break_interval()->reminder_interval
       
//        $job= (new SendBreakNotification())->delay(60);    
//        $this->dispatch($job);

        
    }
}
