<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_breaks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('break_goal');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('reminder_interval');
            $table->integer('user_id');
            $table->string('uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_breaks');
    }
}
