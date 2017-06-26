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
            $table->unsignedInteger('requester');
            $table->foreign('requester')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->unsignedInteger('requestee');
            $table->foreign('requestee')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->boolean('status')->default(false);
            $table->boolean('blocked')->default(false);
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
