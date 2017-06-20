<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_one_id');
            $table->foreign('user_one_id')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->unsignedInteger('user_two_id');
            $table->foreign('user_two_id')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->unsignedInteger('relation_type');
            $table->foreign('relation_type')
              ->references('id')->on('relations')
              ->onDelete('cascade');
            $table->boolean('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('relationships'); 
    }
}
