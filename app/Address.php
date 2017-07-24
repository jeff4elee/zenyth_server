<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public $timestamps = false;
    protected $table = 'addresses';
    protected $fillable = ['profile_id', 'line', 'apt_number', 'city',
        'state', 'zip_code', 'country_code'];

    public function profile()
    {
        return $this->belongsTo('App\Profile', 'profile_id');
    }
}
