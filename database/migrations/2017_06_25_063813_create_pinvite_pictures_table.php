<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinvitePicturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinvite_pictures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pinvite_id')
                ->references('id')->on('pinvites')
                ->onDelete('cascade');
            $table->unsignedInteger('image_id')
                ->references('id')->on('images')
                ->onDelete('cascade');
            $table->timestamp('posted_on');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinvite_pictures');
    }
}
