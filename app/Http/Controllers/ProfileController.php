<?php

namespace App\Http\Controllers;

use App\PhoneNumber;
use App\Address;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $api_token = $request->header('Authorization');
        $profile = User::where('api_token', '=', $api_token)->first()->profile;

        if($request->has('first_name'))
            $profile->first_name = $request['first_name'];

        if($request->has('last_name'))
            $profile->last_name = $request['last_name'];

        if($request->has('phone_number')) {
            // only dealing with U.S. numbers for now
            // TODO: in the future make a method that parses phone number based on country
            $numberStringArr = explode("-" ,$request['phone_number']);
            $country_code = $numberStringArr[0];
            $number = $numberStringArr[1] . $numberStringArr[2] . $numberStringArr[3];

            PhoneNumber::create([
                'profile_id' => $profile->id,
                'country_code' => $country_code,
                'phone_number' => $number
            ]);
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
            $birthdate = \DateTime::createFromFormat('M d, Y', $request['birthday']);
            $profile->date_of_birth = $birthdate;
        }

        $profile->update();

        return response(json_encode([
            'success' => true,
            'data' => $profile
        ]), 200);
    }
}
