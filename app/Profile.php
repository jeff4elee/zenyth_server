<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public $timestamps = false;
    protected $table = 'profiles';
    protected $fillable = ['user_id', 'first_name', 'last_name', 'gender',
        'birthday', 'picture_id'];
    protected $hidden = ['id'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function address()
    {
        return $this->hasOne('App\Address', 'profile_id');
    }

    public function phoneNumber()
    {
        return $this->hasOne('App\PhoneNumber', 'profile_id');
    }

    public function profilePicture()
    {
        return $this->belongsTo('App\Image', 'picture_id');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $profilePicture = $this->profilePicture;
        if($profilePicture)
            $response['picture'] = $profilePicture;

        return $response;
    }
}

