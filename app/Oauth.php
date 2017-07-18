<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oauth extends Model
{
    protected $table = "oauths";
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
