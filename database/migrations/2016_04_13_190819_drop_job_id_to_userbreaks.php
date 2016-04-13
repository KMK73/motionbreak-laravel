<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropJobIdToUserbreaks extends Migration
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
            $table->dropColumn('job_id');

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
