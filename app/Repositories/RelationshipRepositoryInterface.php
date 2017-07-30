<?php


namespace App\Repositories;

/**
 * Interface RelationshipRepositoryInterface
 * @package App\Repositories
 */
interface RelationshipRepositoryInterface
{
    /**
     * Get all users that this user blocked
     * @param $userId
     * @return mixed
     */
    public function getAllBlockedUsers($userId);

    /**
     * Get all friends of this user
     * @param $userId
     * @return mixed
     */
    public function getAllFriends($userId);

    /**
     * Get all friend requests to this user
     * @param $userId
     * @return mixed
     */
    public function getAllFriendRequests($userId);

    /**
     * All relationships that are friends
     * @return mixed
     */
    public function hasFriendship();

    /**
     * All relationships that are blocked
     * @return mixed
     */
    public function isBlocked();

    /**
     * All relationships between two users
     * @param $userOneId
     * @param $userTwoId
     * @return mixed
     */
    public function hasRelationship($userOneId, $userTwoId);

}