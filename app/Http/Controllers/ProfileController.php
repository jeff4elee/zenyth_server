<?php

namespace App\Http\Controllers;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
use App\PhoneNumber;
use App\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->get('user');
        $profile = $user->profile;

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

        if($request->file('image')) {
            $image = Image::find($pin->thumbnail_id);
            $old_filename = $image->filename;
            ImageController::storeImage($request->file('thumbnail'), $image, 'profile_pictures');

            if($old_filename != null)
                Storage::disk('profile_pictures')->delete($old_filename);
            $image->update();
        }

        if($request->has('birthday')) {
            $birthdate = \DateTime::createFromFormat('Y-m-d', $request['birthday']);
            $profile->date_of_birth = $birthdate;
        }

        $profile->update();

        return Response::dataResponse(true, ['profile' => $profile],
            'Successfully updated profile');
    }

}
