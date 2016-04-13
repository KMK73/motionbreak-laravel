<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompletedMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('completed_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('uuid');
            $table->integer('break_goal');
            $table->integer('completed_breaks');
            $table->string('exercise');
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
        Schema::drop('completed_movements');
    }
}
