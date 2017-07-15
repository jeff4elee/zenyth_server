<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pinvite_id');
            $table->foreign('pinvite_id')
                ->references('id')->on('pinvites')
                ->onDelete('cascade');
            $table->boolean('status')->default(false);
            $table->unsignedInteger('invitee_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->timestamp('invited_on')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
}
