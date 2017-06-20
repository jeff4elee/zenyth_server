<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikedEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('liked_entities', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          $table->foreign('user_id')
            ->references('id')->on('users')
            ->onDelete('cascade');
          $table->integer('entity_id')->unsigned();
          $table->foreign('entity_id')
            ->references('id')->on('entities')
            ->onDelete('cascade');
          $table->unique(['user_id', 'entity_id']);
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('liked_entities');
    }
}
