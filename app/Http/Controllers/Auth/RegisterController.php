<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Oauth;
use App\Profile;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticationTrait;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;
    use AuthenticationTrait;

    public function register(Request $request)
    {

        $validator = DataValidator::validateRegister($request);
        if($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $userArr = $this->create($request);

        if($userArr != null)
        {
            $user = $userArr[0];
            $profile = $userArr[1];
            $oauth = new Oauth();
            $oauth->user_id = $user->id;
            $oauth->save();

            Mail::send('confirmation', ['confirmation_code' => $user->confirmation_code]
                , function($message) use ($request, $profile) {
                    $message->to($request['email'], $profile->first_name . " " . $profile->last_name)
                        ->subject('Verify your email address');
                });

            return response(json_encode([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'profile' => $profile
                ]

            ]), 200);
        }

        return response(json_encode(['success' => false]), 200);

    }

    public function oauthRegister(Request $request)
    {
        $validator = DataValidator::validateOauthRegister($request);
        if($validator->fails())
            return response(json_encode([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]), 200);

        $json = $this->oauthValidate($request);
        if($json == null) {
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid access token']
            ]), 200);
        }
        $oauth_type = strtolower($request['oauth_type']);
        $email = null;

        if(isset($json['email'])) {
            $email = $json['email'];
            $user = User::where('email', '=', $email)->first();
            if(!$this->emailConfirmed($user)) {
                $user->delete();
            }
        }
        else if(!isset($json['error'])) {
            return response(json_encode([
                'success' => true,
                'data' => [
                    'email_access' => false,
                    'message' => 'No access to email'
                ]
            ]), 200);
        }
        else {
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid access token']
            ]), 200);
        }

        $user = User::create([
            'email' => $email,
            'username' => $request['username'],
            'password' => Hash::make(str_random(16)),
            'api_token' => $this->generateApiToken(),
            'confirmation_code' => null
        ]);

        if($user != null) {
            $user->token_expired_on = Carbon::now()->addDays(365);
            $user->update();
            $profile = $this->createProfile($request, $user);
            $oauth = new Oauth();
            $oauth->user_id = $user->id;
            if($oauth_type == 'facebook')
                $oauth->facebook = true;
            else if ($oauth_type == 'google')
                $oauth->google = true;

            $oauth->save();

            return response(json_encode([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'api_token' => $user->api_token,
                    'profile' => $profile
                ]
            ]), 200);
        }
    }

    public function emailTaken($email)
    {

        $user = User::where('email', '=', $email)->first();
        $confirmed = false;
        if($user->confirmation_code == null) {
            $confirmed = true;
        }
        return $this->takenResponse($user, $confirmed);

    }

    public function usernameTaken($username)
    {

        $user = User::where('username', '=', $username)->first();
        $confirmed = false;
        if($user->confirmation_code == null) {
            $confirmed = true;
        }
        return $this->takenResponse($user, $confirmed);

    }

    public function takenResponse($user, $confirmed)
    {
        if($user == null) {
            return response(json_encode([
                'success' => true,
                'data' => [
                    'taken' => false
                ]
            ]), 200);
        }
        else {
            return response(json_encode([
                'success' => true,
                'data' => [
                    'taken' => true,
                    'confirmed' => $confirmed
                ]
            ]), 200);
        }
    }

    public function emailConfirmed($user)
    {
        if($user->confirmation_code != null) {
            return false;
        } else {
            return true;
        }
    }

    public function confirm($confirmation_code)
    {

        if( ! $confirmation_code)
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid confirmation code']
            ]), 401);

        $user = User::where('confirmation_code', '=', $confirmation_code)->first();

        if($user == null)
            return response(json_encode([
                'success' => false,
                'errors' => ['Invalid confirmation code']
            ]), 401);

        $user->confirmation_code = null;
        $user->api_token = $this->generateApiToken();
        $user->token_expired_on = Carbon::now()->addDays(365);
        $user->update();

        return response(json_encode([
            'success' => true,
            'message' => 'Account verified'
        ]), 200);

    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request $request
     * @return User
     */
    public function create(Request $request)
    {

        $confirmation_code = str_random(30);
        $user = User::create([
                'email' => $request['email'],
                'username' => $request['username'],
                'password' => Hash::make($request['password']),
                'api_token' => null,
                'confirmation_code' => $confirmation_code
                ]);

        if($user == null)
            return null;

        $profile = $this->createProfile($request, $user);

        return [$user, $profile];

    }

    public function createProfile(Request $request, $user)
    {

        $profile = new Profile();
        $profile->user_id = $user->id;

        if($request->has('gender'))
            $profile->gender = $request['gender'];
        if($request->has('first_name'))
            $profile->first_name = $request['first_name'];
        if($request->has('last_name'))
            $profile->last_name = $request['last_name'];
        if($request->has('birthday')) {
            $birthday = \DateTime::createFromFormat('Y-m-d', $request['birthday']);
            $profile->birthday = $birthday;
        }

        $profile->save();
        return $profile;

    }

}
