<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinpostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinposts', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->foreign('id')
              ->references('id')->on('entities')
              ->onDelete('cascade');
            $table->unique('id');
            $table->string('title');
            $table->text('description');
            $table->double('latitude');
            $table->double('longitude');
            $table->binary('thumbnail');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
              ->references('id')->on('users')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('pinposts');
    }
}
