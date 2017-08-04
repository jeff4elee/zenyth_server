<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPrivacy extends Model
{
    public $timestamps = false;
    protected $table = 'user_privacies';
    protected $fillable = ['user_id', 'email_privacy', 'gender_privacy',
        'birthday_privacy'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
