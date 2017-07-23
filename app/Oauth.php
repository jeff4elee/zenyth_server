<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oauth extends Model
{
    protected $table = "oauths";
    public $timestamps = false;

    protected $fillable = ['user_id', 'facebook', 'google'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        $this->update();
    }

    public function setFacebook($boolean)
    {
        $this->facebook = $boolean;
        $this->update();
    }

    public function setGoogle($boolean)
    {
        $this->google = $boolean;
        $this->update();
    }

}
