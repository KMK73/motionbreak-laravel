<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetCompletedMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:completedMovements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the completed movements of users nightly.';

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
        //reset the completed breaks of all users
        $movements = CompletedMovement::all();
        $movements->completed_breaks = 0;
        $movements->save();
        return response()->json($movements); 

    }
}
