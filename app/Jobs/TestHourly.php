<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\SelfHandling;

class TestHourly extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        ////pass in the user that launched this job through the controller
        $this->user_id = $user_id; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //queue the job with this user id    
        \Artisan::queue('test:hourly', [
            'user' => 18]);
    }
}
