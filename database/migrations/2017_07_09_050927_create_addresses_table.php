<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('profile_id');
            $table->foreign('profile_id')
                ->references('user_id')->on('profiles')
                ->onDelete('cascade');
            $table->string('line', 60);
            $table->string('apt_number', 20)->nullable();
            $table->string('city', 20);
            $table->string('state', 20);
            $table->string('zip_code', 10);
            $table->string('country_code', 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
