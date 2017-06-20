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
        'name', 'email', 'password', 'created_on', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function pinpost()
    {
      return $this->hasMany('App\Pinpost', 'user_id');
    }

    public function pinvite() {
        return $this->hasMany('App\Pinvite', 'user_id');
    }

    public function likable_entity() {
        return $this->hasMany('App\Likable_entity', 'user_id');
    }

    public function commentable_entity() {
        return $this->hasMany('App\Commentable_entity', 'user_id');
    }
}

