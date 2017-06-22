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
