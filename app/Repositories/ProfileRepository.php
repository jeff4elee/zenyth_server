<?php

namespace App\Repositories;

use App\Exceptions\Exceptions;
use Illuminate\Database\Eloquent\Model;

class ProfileRepository extends Repository
{
    function model()
    {
        return 'App\Profile';
    }

    /**
     * @param $request
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function create($request)
    {
        $gender = $request->input('gender');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');

        if($request->has('birthday')) // Format birthday
            $birthday = \DateTime::createFromFormat('Y-m-d', $request->input('birthday'));
        else
            $birthday = null;

        $user = $request->get('user');
        $profile = $this->model->create([
            'user_id' => $user->id,
            'gender' => $gender,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'birthday' => $birthday
        ]);

        if($profile)
            return $profile;
        else
            Exceptions::unknownErrorException(OBJECT_FAIL_TO_CREATE);
    }

    /**
     * @param $request
     * @param $model
     * @param string $attribute
     * @return mixed
     */
    public function update($request, $model = null, $attribute = 'id')
    {
        if($model instanceof Model)
            $profile = $model;
        else if($model != null)
            $profile = $this->model->where($attribute, '=', $model)->first();
        else
            Exceptions::invalidParameterException(EITHER_MODEL_OR_ID);

        $data = [];

        if($request->has('first_name'))
            $data['first_name'] = $request['first_name'];

        if($request->has('last_name'))
            $data['last_name'] = $request['last_name'];

        if($request->has('gender'))
            $data['gender'] = $request['gender'];

        if($request->has('birthday')) {
            $birthday = \DateTime::createFromFormat('Y-m-d', $request['birthday']);
            $data['birthday'] = $birthday;
        }
        if($request->has('biography')) {
            $data['biography'] = $request['biography'];
        }

        $profile->update($data);
        return $profile;
    }
}