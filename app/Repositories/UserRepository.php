<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use App\Http\Controllers\Auth\AuthenticationTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserRepository extends Repository
                    implements UserRepositoryInterface
{
    use AuthenticationTrait;

    public function model()
    {
        return 'App\User';
    }

    public function create(Request $request)
    {
        if($request->is('api/oauth/register')) {
            $password = Hash::make(str_random(16));
            $confirmation_code = null;
        }
        else {
            $password = Hash::make($request['password']);
            $confirmation_code = str_random(30);
        }

        $user = User::create([
            'email' => $request['email'],
            'username' => $request['username'],
            'password' => $password,
            'api_token' => $this->generateApiToken(),
            'token_expired_on' => Carbon::now()->addDays(365),
            'confirmation_code' => $confirmation_code
        ]);

        if($user)
            return $user;
        else
            Exceptions::unknownErrorException('Unable to create user');
    }

    /**
     * Join users table to profiles table on key user_id
     * @return mixed
     */
    public function joinProfiles()
    {
        $query = $this->model->join('profiles', 'profiles.user_id', '=', 'users.id');
        $this->model = $query;
        return $this;
    }

    /**
     * Get all results that have similar first name
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeFirstName($keyword, $orQuery = false)
    {
        if($orQuery)
            $query = $this->model->orWhere('profiles.first_name', 'like',
                '%'.$keyword.'%');
        else
            $query = $this->model->where('profiles.first_name', 'like',
                '%'.$keyword.'%');

        $this->model = $query;
        return $this;
    }

    /**
     * Get all results that have similar last name
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeLastName($keyword, $orQuery = false)
    {
        if($orQuery)
            $query = $this->model->orWhere('profiles.last_name', 'like',
                '%'.$keyword.'%');
        else
            $query = $this->model->where('profiles.last_name', 'like',
                '%'.$keyword.'%');

        $this->model = $query;
        return $this;
    }

    /**
     * Get all results that have similar username
     * @param $keyword
     * @param $orQuery
     * @return mixed
     */
    public function likeUsername($keyword, $orQuery = false)
    {
        if($orQuery)
            $query = $this->model->orWhere('users.username', 'like',
                '%'.$keyword.'%');
        else
            $query = $this->model->where('users.username', 'like',
                '%'.$keyword.'%');

        $this->model = $query;
        return $this;
    }

    /**
     * Join the relationships table
     * @param $option , either requester or requestee
     * @return mixed
     */
    public function joinRelationships($option)
    {
        $query = $this->model
            ->leftJoin('relationships', 'users.id', '=', $option);

        $this->model = $query;
        return $this;
    }


}