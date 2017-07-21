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
}
