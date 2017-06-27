<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class User extends Model implements Authenticatable
{
    use AuthenticableTrait;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password', 'email', 'api_token', 'token_expired_on'
    ];

    protected $hidden = [
        'password', 'api_token', 'token_expired_on'
    ];

    public $timestamps = false;

    public function pinposts()
    {
        return $this->hasMany('App\Pinpost', 'user_id');
    }

    public function pinvites()
    {
        return $this->hasMany('App\Pinvite', 'creator_id');
    }

    public function likes()
    {
        return $this->hasMany('App\Like', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment', 'user_id');
    }

    public function profile()
    {
        return $this->hasOne('App\Profile', 'user_id');
    }

}

