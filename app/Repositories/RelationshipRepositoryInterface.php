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
    public function getAllFollowers($userId);

    /**
     * Get all friend requests to this user
     * @param $userId
     * @return mixed
     */
    public function getAllFollowerRequests($userId);

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
    public function getFollowRelationship($requesterId, $requesteeId);

}