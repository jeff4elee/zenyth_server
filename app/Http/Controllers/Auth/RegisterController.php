<?php

namespace App\Http\Controllers\Auth;

use App\User;
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

            Mail::send('confirmation', ['confirmation_code' => $user->confirmation_code]
                , function($message) use ($request) {
                    $message->to($request['email'], $request['username'])
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

        $user = User::create([
            'email' => $request['email'],
            'username' => $request['username'],
            'password' => Hash::make(str_random(16)),
            'api_token' => $this->generateApiToken(),
            'confirmation_code' => null
        ]);
        $profile = $this->createProfile($request, $user);

        if($user != null) {
            return response(json_encode([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'api_token' => $user->api_token,
                    'profile' => [
                        'first_name' => $profile->first_name,
                        'last_name' => $profile->last_name,
                        'gender' => $profile->gender,
                        'birthday' => date_format($profile->birthday, "Y-m-d")
                    ]
                ]
            ]), 200);
        }
    }

    public function emailTaken($email)
    {

        $user = User::where('email', '=', $email)->first();
        if($user == null) {
            return response(json_encode([
                'success' => true,
                'data' => false
            ]), 200);
        }
        else {
            return response(json_encode([
                'success' => true,
                'data' => true
            ]), 200);
        }

    }

    public function usernameTaken($username)
    {

        $user = User::where('username', '=', $username)->first();
        if($user == null) {
            return response(json_encode([
                'success' => true,
                'data' => false
            ]), 200);
        }
        else {
            return response(json_encode([
                'success' => true,
                'data' => true
            ]), 200);
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
            $birthday = \DateTime::createFromFormat('M d, Y', $request['birthday']);
            $profile->birthday = $birthday;
        }

        $profile->save();
        return $profile;

    }

}
