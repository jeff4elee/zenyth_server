<?php


namespace App\Repositories;


/**
 * Interface UserRepositoryInterface
 * @package App\Repositories
 */
interface UserRepositoryInterface
{
    /**
     * Join users table to profiles table on key user_id
     * @return mixed
     */
    public function joinProfiles();

    /**
     * Get all results that have similar first name
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeFirstName($keyword, $orQuery = false);

    /**
     * Get all results that have similar last name
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeLastName($keyword, $orQuery = false);

    /**
     * Get all results that have similar username
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeUsername($keyword, $orQuery = false);

    /**
     * Join the relationships table
     * @param $option, either requester or requestee
     * @return mixed
     */
    public function joinRelationships($option);

    /**
     * Get all users that have id that is in this array
     * @param $idArray
     * @return mixed
     */
    public function allUsersInIdArray($idArray);
}