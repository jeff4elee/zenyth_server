<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinvites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->foreign('entity_id')
                ->references('id')->on('entities')
                ->onDelete('cascade');
            $table->unique('entity_id');

            $table->string('title');
            $table->double('latitude');
            $table->double('longitude');
            $table->text('description');

            $table->unsignedInteger('thumbnail_id')
                ->nullable()->default(null);
            $table->foreign('thumbnail_id')
                ->references('id')->on('images')
                ->onDelete('set null');

            $table->unsignedInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->dateTime('event_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinvites');
    }
}
