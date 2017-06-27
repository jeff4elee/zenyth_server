<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public $timestamps = false;
    protected $fillable = ['first_name', 'last_name', 'gender'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
