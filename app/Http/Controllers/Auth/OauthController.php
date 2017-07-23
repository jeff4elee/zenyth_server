<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ResponseHandler as Response;
use App\Exceptions\Exceptions;
use App\User;
use App\Profile;
use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use App\Http\Requests\DataValidator;

class OauthController extends RegisterController
{
    use AuthenticationTrait;
    protected $mergeGoogle = 'A Google account with the same email has already been created. Do you want to merge?';
    protected $mergeFacebook = 'A Facebook account with the same email has already been created. Do you want to merge?';
    protected $mergeAccount = 'An account with the same email has already been created. Do you want to merge?';

    /**
     * Logs the user in with oauth
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function oauthLogin(Request $request)
    {

        // Validates to see if request contains email and oauth_type
        $validator = DataValidator::validateOauthLogin($request);
        if($validator->fails())
            return Response::validatorErrorResponse($validator);

        $oauth_type = strtolower($request['oauth_type']);
        $email = $request['email'];
        $json = $request['json'];

        // Gets user with the same email
        $user = User::where('email', '=', $email)->first();
        if($user != null) {
            $oauth = $user->oauth;
            $profile = $user->profile;
            $data = [
                'user' => $user,
                'api_token' => $user->api_token,
                'oauth_type' => $oauth_type
            ];

            return $this->processOauth($oauth_type, $profile, $json, $oauth, $data, $request);

        }

    }


    /**
     * Processes an oauth request
     *
     * @param $oauth_type
     * @param $profile
     * @param $json
     * @param $oauth
     * @param $data
     * @param $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function processOauth($oauth_type, $profile, $json, $oauth, $data, $request) {
        // Previously logged in with google but now logging in with facebook
        if($oauth_type == 'facebook' &&
            !$oauth->facebook && $oauth->google) {
            if($request->has('merge') && $request['merge']) {
                // merges to facebook account
                $oauth->setFacebook(true);
                $this->mergeInformation($profile, $json, $oauth_type);
                return Response::dataResponse($data, 'Successfully merged account');
            }
            return Response::dataResponse(false, ['mergeable' => true], $this->mergeGoogle);
        }
        // Previously logged in with facebook but now logging in with google
        else if($oauth_type == 'google' &&
            !$oauth->google && $oauth->facebook) {
            if($request->has('merge') && $request['merge']) {
                // merges to google account
                $oauth->setGoogle(true);
                $this->mergeInformation($profile, $json, $oauth_type);
                return Response::dataResponse($data, 'Successfully merged account');
            }
            return Response::dataResponse(false, ['mergeable' => true], $this->mergeFacebook);
        }

        // Previously created an account on the app but now logging in through oauth
        else if(!$oauth->facebook && !$oauth->google) {
            if($request->has('merge') && $request['merge']) {
                if($oauth_type == 'google')
                    $oauth->setGoogle(true);
                else if($oauth_type == 'facebook')
                    $oauth->setFacebook(true);

                $this->mergeInformation($profile, $json, $oauth_type);
                return Response::dataResponse($data, 'Successfully merged account');
            }
            return Response::dataResponse(false, ['mergeable' => true], $this->mergeAccount);
        }
        else {
            return Response::dataResponse(true, $data, 'Successfully logged in');
        }
    }

    /**
     * Merges information when logged in with oauth
     *
     * @param Profile $profile
     * @param $json
     * @param $oauth_type
     */
    public function mergeInformation(Profile $profile, $json, $oauth_type)
    {
        $last_name_key = null;
        $first_name_key = null;

        if($oauth_type == 'google') {
            $last_name_key = 'family_name';
            $first_name_key = 'given_name';
        }
        else if($oauth_type == 'facebook') {
            $last_name_key = 'last_name';
            $first_name_key = 'first_name';
        }

        if(isset($json['gender']) && $profile->gender == null) {
            $profile->gender = $json['gender'];
        }
        if(isset($json[$first_name_key]) && $profile->first_name == null) {
            $profile->first_name = $json[$first_name_key];
        }
        if(isset($json[$last_name_key]) && $profile->last_name == null) {
            $profile->last_name = $json[$last_name_key];
        }
        if(isset($json['picture']) && $profile->image_id == null) {
            $url = null;
            if($oauth_type == 'facebook')
                $url = $json['picture']['data']['url'];
            else if($oauth_type == 'google')
                $url = $json['picture'];

            $image = ImageController::storeProfileImage($url);
            if($image != null)
                $profile->image_id = $image->id;
        }

        $profile->update();
    }

}