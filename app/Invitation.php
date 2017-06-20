<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['invited_on'];

    public function pinvite() {
        return $this->belongsTo('App\Pinvite', 'pinvite_id');
    }

    public function invitee() {
        return $this->belongsTo('App\User', 'invitee_id');
    }
}
