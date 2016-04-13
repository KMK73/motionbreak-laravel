<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReADDJobIdToUserbreaks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_breaks', function (Blueprint $table) {
            //
         $table->integer('job_id')->nullable();	

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_breaks', function (Blueprint $table) {
            //
        });
    }
}
