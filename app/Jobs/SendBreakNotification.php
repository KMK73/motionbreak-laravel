<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;


class SendBreakNotification extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

//    protected $user; 
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //pass in the user that launched this job through the controller
//        $this->user = $user; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//         $user = User::find(1);
        //
//        $this->user->break_interval()->reminder_interval;
        
        \Artisan::queue('notification:test', [
            'user' =>1]);
        //create another job after the the above gets queued. this will create a never ending infinite loop.
//$this->user->break_interval()->reminder_interval
       
//        $job= (new SendBreakNotification())->delay(60);    
//        $this->dispatch($job);

        
    }
}
