<?php


namespace App\Repositories;


class UserPrivacyRepository extends Repository
{
    function model()
    {
        return 'App\UserPrivacy';
    }

    public function update($request, $model = null, $attribute = 'id')
    {
        $data = [];
        if($request->has('email_privacy')) {
            $data = array_add($data, 'email_privacy', strtolower
            ($request['email_privacy']));
        }

        if($request->has('gender_privacy'))
            $data = array_add($data, 'gender_privacy', strtolower
            ($request['gender_privacy']));

        if($request->has('birthday_privacy'))
            $data = array_add($data, 'birthday_privacy', strtolower
            ($request['birthday_privacy']));

        return $this->model->where($attribute, '=', $model)->update($data);
    }
}