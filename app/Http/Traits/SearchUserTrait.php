<?php

namespace App\Http\Traits;

use App\Profile;
use App\Relationship;
use App\User;

/**
 * Trait SearchUserTrait
 * @package App\Http\Traits
 */
trait SearchUserTrait
{

    /**
     * Get id of users that are friends to the input user
     * @param $user
     * @return mixed , id of friends
     */
    public function getAllFriendsId($user)
    {
        return array_values($user->friendsId());
    }

    /**
     * Get id of users that are mutual friends to the input user
     * @param $friendsId
     * @return mixed , id of mutual friends
     */
    public function getAllMutualFriendsId($friendsId)
    {
        $mutualFriends1 = Relationship::select('requestee as user_id')
            ->whereIn('requester', $friendsId);

        $mutualFriends2 = Relationship::select('requester as user_id')
            ->whereIn('requestee', $friendsId);

        return $mutualFriends1->union($mutualFriends2)->get()->pluck('user_id')
            ->all();
    }

    /**
     * Get id of users that have similar names to input name, excluding the
     * input user
     * @param $keyword
     * @return mixed , id of users that have similar names
     */
    public function getRelevantResults($keyword, $userId)
    {
        // This query contains all search results
        // but we need to filter by relevance
        $query = User::select('users.id')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->where([
                ['users.username', 'like', '%' . $keyword . '%'],
                ['users.id', '!=', $userId]
            ])
            ->orWhere([
                ['profiles.first_name', 'like', '%' . $keyword . '%'],
                ['users.id', '!=', $userId]
            ])
            ->orWhere([
                ['profiles.last_name', 'like', '%' . $keyword . '%'],
                ['users.id', '!=', $userId]
            ]);

        return $query->get()->pluck('id')->all();
    }

    /**
     * Return an array containing the users'id that resulted from the search
     * @param $allResultsId
     * @param $friendsId
     * @return array containing users' id's
     */
    public function inclusionExclusion(array $allResultsId, array $friendsId,
                                       array $mutualFriendsId)
    {
        /* Retain only friends'id that have names similar to the name
        searched */
        $friendsId = array_filter($friendsId, function ($id)
                                                use ($allResultsId) {
            return in_array($id, $allResultsId);
        });

        /* Retain only mutual friends'id that have names similar to the name
         searched */
        $mutualFriendsId = array_filter($mutualFriendsId, function ($id)
                                                use ($allResultsId) {
            return in_array($id, $allResultsId);
        });

        /* Sort the array in the order friends_id, mutual_friends_id, all
        the rest */
        $resultArr = array_merge($friendsId, $mutualFriendsId);

        /* Get the rest of the id's that are not in the group friends and
        mutual friends */
        $restSimilarNames = array_diff($allResultsId, $resultArr);

        $resultArr = array_merge($resultArr, $restSimilarNames);
        return $resultArr;
    }

}