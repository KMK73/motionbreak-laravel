<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\User; //user model
use App\CompletedMovement; //completed movement model
use App\UserBreak; //user breaks model
use App\Jobs\Job;

class ResetCompletedBreaks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:breaks';
    //protected $signature = 'notification:test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset completed movements nightly.';

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
        $movements = CompletedMovement::all();
        $completedBreaks = $movements->completed_breaks;
        $completedBreaks = 0; 
        $movements->save();

        $this->info('completed breaks reset');        

        return response()->json($movementSettings); 
        
    }//end of handle function
}
