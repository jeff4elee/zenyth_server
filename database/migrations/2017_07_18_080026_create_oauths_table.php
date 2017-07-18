<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauths', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')
                ->references('id')->on('users');
            $table->boolean('facebook')->default(false);
            $table->boolean('google')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauths');
    }
}
