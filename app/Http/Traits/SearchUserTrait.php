<?php

namespace App\Http\Traits;

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
    public function getAllFollowerIds($user)
    {
        if($user->followerIds() === NULL){
            return array();
        }

        return array_values($user->followerIds);
    }

    public function getAllFollowingIds($user)
    {
        if($user->followingIds() === NULL){
            return array();
        }

        return array_values($user->followingIds);
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
     * @param $followingIds
     * @return array containing users' id's
     */
    public function inclusionExclusion(array $allResultsId, array $followingIds,
                                       array $followerIds)
    {
        /* Retain only following ids that have names similar to the name
        searched */
        $followingIds = array_filter($followingIds, function ($id)
                                                use ($allResultsId) {
            return in_array($id, $allResultsId);
        });

        /* Retain only follower ids that have names similar to the name
         searched */
        $followerIds = array_filter($followerIds, function ($id)
                                                use ($allResultsId) {
            return in_array($id, $allResultsId);
        });

        /* Sort the array in the order following ids, follower ids, all
        the rest */
        $resultArr = array_merge($followingIds, $followerIds);

        /* Get the rest of the id's that are not in the group following/follower */
        $restSimilarNames = array_diff($allResultsId, $resultArr);

        $resultArr = array_merge($resultArr, $restSimilarNames);
        return $resultArr;
    }

}