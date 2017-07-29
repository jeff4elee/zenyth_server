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
        'username', 'password', 'email', 'token_expired_on', 'api_token', 'confirmation_code'
    ];

    protected $hidden = [
        'password', 'token_expired_on', 'api_token', 'confirmation_code',
        'remember_token'
    ];

    public $timestamps = false;

    public function passwordReset()
    {
        return $this->hasMany('App\PasswordReset', 'email');
    }

    public function pinposts()
    {
        return $this->hasMany('App\Pinpost', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany('App\Like', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany('App\Reply', 'user_id');
    }

    public function profile()
    {
        return $this->hasOne('App\Profile', 'user_id');
    }

    public function oauth()
    {
        return $this->hasOne('App\Oauth', 'user_id');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $response['profile'] = $this->profile->makeHidden('user_id');
        $response['oauth'] = $this->oauth;
        return $response;
    }

    public function name()
    {
        $profile = $this->profile;
        $name = $profile->first_name . " " . $profile->last_name;
        return $name;
    }

    /*
     * Relationships where this user is the requestee
     */
    public function requesteeRelationships()
    {
        return $this->hasMany('App\Relationship', 'requestee');
    }

    /*
     * Relationships where this user is the requester
     */
    public function requesterRelationships()
    {
        return $this->hasMany('App\Relationship', 'requester');
    }

    /**
     * Get the array containing all the ids of friends
     * @return array
     */
    public function friendsId()
    {
        $requesterRelationships = $this->requesterRelationships;
        $requesteeRelationships = $this->requesteeRelationships;
        $idArray = array();

        foreach($requesterRelationships as $relationship) {
            if($relationship->status)
                array_push($idArray, $relationship->requestee);
        }
        foreach($requesteeRelationships as $relationship) {
            if($relationship->status)
                array_push($idArray, $relationship->requester);
        }
        return $idArray;
    }

}

