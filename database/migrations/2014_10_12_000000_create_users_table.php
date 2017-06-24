<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('api_token', 60)->unique()
                                                            ->nullable();

            $token_expired_on = Carbon::now()->addDays(30);
            $table->dateTime('token_expired_on')
                    ->default($token_expired_on);

            $table->rememberToken();

            $table->dateTime('created_on')
                    ->default(Carbon::now());
            $table->dateTime('updated_on')
                    ->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('users');
    }

}
