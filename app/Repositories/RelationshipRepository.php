<?php


namespace App\Repositories;


class RelationshipRepository extends Repository
                            implements RelationshipRepositoryInterface
{
    function model()
    {
        return 'App\Relationship';
    }

    /**
     * Get all users that this user blocked
     * @param $userId
     * @return mixed
     */
    public function getAllBlockedUsers($userId)
    {
        $query = $this->model->select('users.*')
            ->join('users', 'users.id', '=', 'requestee')
            ->where('requester', '=', $userId)
            ->where('blocked', '=', true);

        $this->model = $query;
        return $this;
    }

    /**
     * Get all friends of this user
     * @param $userId
     * @return mixed
     */
    public function getAllFriends($userId)
    {
        $queryOne = $this->model->select('users.*')
            ->join('users', 'users.id', '=', 'requester')
            ->where('requestee', '=', $userId)
            ->where('status', '=', true);

        $queryTwo = $this->model->select('users.*')
            ->join('users', 'users.id', '=', 'requestee')
            ->where('requester', '=', $userId)
            ->where('status', '=', true);;

        $query = $queryOne->union($queryTwo);
        $this->model = $query;
        return $this;
    }

    /**
     * Get all friend requests to this user
     * @param $userId
     * @return mixed
     */
    public function getAllFriendRequests($userId)
    {
        $query = $this->model->select('users.*')
            ->join('users', 'users.id', '=', 'requester')
            ->where('requestee', '=', $userId)
            ->where('status', '=', false);

        $this->model = $query;
        return $this;
    }

    /**
     * All relationships that are friends
     * @return mixed
     */
    public function hasFriendship()
    {
        $query = $this->model->where('status', '=', true);
        $this->model = $query;
        return $this;
    }

    /**
     * All relationships between two users
     * @param $userOneId
     * @param $userTwoId
     * @return mixed
     */
    public function hasRelationship($userOneId, $userTwoId)
    {
        $query = $this->model->where([
            ['requester', '=', $userOneId],
            ['requestee', '=', $userTwoId]
        ])->orWhere([
            ['requestee', '=', $userOneId],
            ['requester', '=', $userTwoId]
        ]);

        $this->model = $query;
        return $this;
    }


}