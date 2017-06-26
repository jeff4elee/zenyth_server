<?php

namespace App\Http\Traits;

use App\User;
use App\Relationship;

/**
 * Trait SearchUserTrait
 * @package App\Http\Traits
 */
trait SearchUserTrait
{

    /**
     * Gets id of users that are friends to the input user
     *
     * @param $user_id , input user
     * @return mixed , id of friends
     */
    public function getAllFriendsId($user_id)
    {
        $friends1 = Relationship::select('requestee as user_id')
            ->where([
                ['requester', '=', $user_id],
                ['status', '=', true]
            ]);
        $friends2 = Relationship::select('requester as user_id')
            ->where([
                ['requestee', '=', $user_id],
                ['status', '=', true]
            ]);
        return $friends1->union($friends2)->get()->pluck('user_id');
    }

    /**
     * Gets id of users that are mutual friends to the input user
     *
     * @param $user_id , input user
     * @return mixed , id of mutual friends
     */
    public function getAllMutualFriendsId($user_id)
    {
        $friends1 = Relationship::select('requestee as friend_id')
            ->where([
                ['requester', '=', $user_id],
                ['status', '=', true]
            ]);
        $friends2 = Relationship::select('requester as friend_id')
            ->where([
                ['requestee', '=', $user_id],
                ['status', '=', true]
            ]);
        $friends_id = $friends1->union($friends2)->get();

        $mutualFriends1 = Relationship::select('requestee as user_id')
            ->whereIn('requester', $friends_id);

        $mutualFriends2 = Relationship::select('requester as user_id')
            ->whereIn('requestee', $friends_id);

        return $mutualFriends1->union($mutualFriends2)->get()->pluck('user_id');
    }

    /**
     * Gets id of users that have similar names to input name, excluding the
     * input user
     *
     * @param $user_id , input user
     * @param $name , input name
     * @return mixed , id of users that have similar names
     */
    public function similarTo($user_id, $name)
    {
        return User::select('users.id')->where([
            ['users.name', 'like', '%' . $name . '%'],
            ['users.id', '!=', $user_id]
        ])->get()->pluck('id');
    }

}