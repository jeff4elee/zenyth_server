<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phone_number extends Model
{
    protected $fillable = ['profile_id', 'country_code', 'phone_number'];
    public $timestamps = false;

    public function profile()
    {
        return $this->belongsTo('App\Profile', 'profile_id');
    }

    public function user()
    {
        return $this->profile->user;
    }
}
