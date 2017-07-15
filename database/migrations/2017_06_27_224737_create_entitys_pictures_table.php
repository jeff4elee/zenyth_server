<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateEntitysPicturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entitys_pictures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id')
                ->references('id')->on('entities')
                ->onDelete('cascade');
            $table->unsignedInteger('image_id')
                ->references('id')->on('images')
                ->onDelete('cascade');
            $table->timestamp('posted_on')
                ->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entitys_pictures');
    }
}
