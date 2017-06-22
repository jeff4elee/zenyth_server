<?php

namespace App;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Model;

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
        'name', 'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token', 'remember_token', 'created_on'
    ];

    public function pinposts()
    {
      return $this->hasMany('App\Pinpost', 'user_id');
    }

    public function pinvites() {
        return $this->hasMany('App\Pinvite', 'user_id');
    }

    public function likes() {
        return $this->hasMany('App\Like', 'user_id');
    }

    public function comments() {
        return $this->hasMany('App\Comment', 'user_id');
    }
}

