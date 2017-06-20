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
            $table->integer('requestor_id')->unsigned();
            $table->foreign('requestor_id')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->integer('requestee_id')->unsigned();
            $table->foreign('requestee_id')
              ->references('id')->on('users')
              ->onDelete('cascade');
            $table->integer('relation_type')->unsigned();
            $table->foreign('relation_type')
              ->references('id')->on('relations')
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
      Schema::dropIfExists('relationships'); 
    }
}
