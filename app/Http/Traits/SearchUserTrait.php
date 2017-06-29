<?php

namespace App\Http\Traits;

use App\Profile;
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
        $friends_id = $this->getAllFriendsId($user_id);

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
        if($user_id != null) {
            return Profile::select('profiles.user_id')
                ->where([
                    ['profiles.first_name', 'like', '%' . $name . '%'],
                    ['profiles.user_id', '!=', $user_id]
                ])
                ->orWhere([
                    ['profiles.last_name', 'like', '%' . $name . '%'],
                    ['profiles.user_id', '!=', $user_id]
                ])
                ->get()->pluck('user_id');
        }
        else {
            return Profile::select('profiles.user_id')
                ->where('profiles.first_name', 'like', '%' . $name . '%')
                ->orWhere('profiles.last_name', 'like', '%' . $name . '%')
                ->get()->pluck('user_id');
        }
    }

    /**
     * Returns an array containing the users'id that resulted from the search
     *
     * @param $user_id , person searching
     * @param $name , name to search
     * @return array , array containing result users'id
     */
    public function searchUserId($user_id, $name)
    {
        $name = str_replace(' ', '%', $name);

        /* Gets all the user id where names are similar */
        $similar_names_id = $this->similarTo($user_id, $name)->toArray();

        /* Gets all the user id where users are friends of logged in user */
        $friends_id = $this->getAllFriendsId($user_id)->toArray();

        /* Gets all the user id where users and mutual friends of logged
        in user */
        $mutual_friends_id = $this->getAllMutualFriendsId($user_id)->toArray();

        /* Retains only friends'id that have names similar to the name
        searched */
        $friends_id = array_filter($friends_id, function ($id)
                                                use ($similar_names_id) {
            return in_array($id, $similar_names_id);
        });

        /* Retains only mutual friends'id that have names similar to the name
         searched */
        $mutual_friends_id = array_filter($mutual_friends_id, function ($id)
                                                use ($similar_names_id) {
            return in_array($id, $similar_names_id);
        });

        /* Sorts the array in the order friends_id, mutual_friends_id, all
        the rest */
        $result_id = array_merge($friends_id, $mutual_friends_id);
        $rest_similar_names = array_diff($similar_names_id, $result_id);
        $result_id = array_merge($result_id, $rest_similar_names);
        return $result_id;
    }

}