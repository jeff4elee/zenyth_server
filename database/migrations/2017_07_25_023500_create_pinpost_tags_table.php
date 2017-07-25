<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinpostTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinpost_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pinpost_id')
                ->references('id')->on('pinposts')
                ->onDelete('cascade');
            $table->unsignedInteger('tag_id')
                ->references('id')->on('tags')
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
        Schema::dropIfExists('pinpost_tags');
    }
}
