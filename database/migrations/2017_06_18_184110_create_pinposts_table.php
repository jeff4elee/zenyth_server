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
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->foreign('entity_id')
                ->references('id')->on('entities')
                ->onDelete('cascade');
            $table->unique('entity_id');
            $table->string('title');
            $table->text('description');
            $table->double('latitude');
            $table->double('longitude');
            $table->unsignedInteger('thumbnail_id')
                ->nullable()->default(null);
            $table->foreign('thumbnail_id')
                ->references('id')->on('images')
                ->onDelete('set null');
            $table->unsignedInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->timestamp('updated_at');
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
