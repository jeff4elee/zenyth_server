<?php

namespace App\Repositories;

use App\Address;
use App\Exceptions\Exceptions;
use App\PhoneNumber;
use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProfileRepository extends Repository
{
    function model()
    {
        return 'App\Profile';
    }

    public function create(Request $request)
    {
        $gender = $request->input('gender');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');

        if($request->has('birthday')) // Format birthday
            $birthday = \DateTime::createFromFormat('Y-m-d', $request->input('birthday'));
        else
            $birthday = null;

        $user = $request->get('user');
        $profile = Profile::create([
            'user_id' => $user->id,
            'gender' => $gender,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'birthday' => $birthday
        ]);

        if($profile){
            Cache::put('profile' . $profile->id, $profile);
            return $profile;
        }
        else
            Exceptions::unknownErrorException('Unable to create profile');
    }

    public function read($id, $fields=['*']){

        $key = 'profile' . $id;

        if(Cache::has($key)){
            return Cache::get($key);
        } else {
            return parent::read($id, $fields);
        }

    }

    public function update(Request $request, $id, $attribute = 'id')
    {
        $profile = $this->model->where($attribute, '=', $id)->first();
        if($request->has('first_name'))
            $profile->first_name = $request['first_name'];

        if($request->has('last_name'))
            $profile->last_name = $request['last_name'];

        if($request->has('phone_number')) {
            // only dealing with U.S. numbers for now
            // TODO: in the future make a method that parses phone number based on country
            $numberStringArr = explode("-", $request['phone_number']);
            $country_code = $numberStringArr[0];
            $number = $numberStringArr[1] . $numberStringArr[2] . $numberStringArr[3];

            if($phoneNumber = $profile->phoneNumber) {
                $phoneNumber->update([
                    'country_code' => (int)$country_code,
                    'phone_number' => $number
                ]);
            }
            else {
                PhoneNumber::create([
                    'profile_id' => $profile->id,
                    'country_code' => (int)$country_code,
                    'phone_number' => $number
                ]);
            }
        }

        if($request->has('gender')) {
            $profile->gender = $request['gender'];
        }

        if($request->has('address')) {
            $address = $request['address'];

            Address::create([
                'profile_id' => $profile->id,
                'line' => $address['line'],
                'apt_number' => $address['apt_number'],
                'city' => $address['city'],
                'state' => $address['state'],
                'zip_code' => $address['zip_code'],
                'country_code' => $address['country_code']
            ]);
        }

        if($request->has('birthday')) {
            $birthday = \DateTime::createFromFormat('Y-m-d', $request['birthday']);
            $profile->birthday = $birthday;
        }

        $profile->update();

        Cache::put('profile' . $profile->id, $profile);

        return $profile;
    }
}