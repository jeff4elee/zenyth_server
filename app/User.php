<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Auth\AuthenticationTrait;

class User extends Model
{
    use AuthenticationTrait;

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

    public function userPrivacy()
    {
        return $this->hasOne('App\UserPrivacy', 'user_id');
    }

    public function toArray()
    {
        $response = parent::toArray();
        $profile = $this->profile;

        if(!in_array('first_name', $this->hidden))
            $response['first_name'] = $profile->first_name;
        if(!in_array('last_name', $this->hidden))
            $response['last_name'] = $profile->last_name;
        if(!in_array('gender', $this->hidden))
            $response['gender'] = $profile->gender;
        if(!in_array('birthday', $this->hidden))
            $response['birthday'] = $profile->birthday;

        $picture = $profile->profilePicture;
        $response['picture'] = $picture;

        if(!in_array('friends', $this->hidden))
            $response['friends'] = $this->friendsCount();
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

    public function friendsCount()
    {
        return Relationship::where([
            ['requester', '=', $this->id],
            ['status', '=', true]
        ])->orWhere([
            ['requestee', '=', $this->id],
            ['status', '=', true]
        ])->count();
    }

    public function blockedUsersId()
    {
        $requesterRelationships = $this->requesterRelationships;
        $idArray = array();

        foreach($requesterRelationships as $relationship) {
            if($relationship->blocked)
                array_push($idArray, $relationship->requestee);
        }

        return $idArray;
    }

    public function friendRequestsUsersId()
    {
        $requesteeRelationships = $this->requesteeRelationships;
        $idArray = array();

        foreach($requesteeRelationships as $relationship) {
            if(!$relationship->blocked && !$relationship->status)
                array_push($idArray, $relationship->requester);
        }

        return $idArray;
    }

}

