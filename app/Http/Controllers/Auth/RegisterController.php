<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Exceptions;
use App\Exceptions\ResponseHandler as Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ImageController;
use App\Oauth;
use App\Profile;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Registers user
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $userArr = $this->create($request);

        if($userArr != null)
        {
            $user = $userArr[0];
            $profile = $userArr[1];
            Oauth::create(['user_id' => $user->id]);

            // Send confirmation email
            $name = $profile->first_name . " " . $profile->last_name;
            $infoArray = ['confirmation_code' => $user->confirmation_code];
            $subject = 'Verify your email address';
            $this->sendEmail('confirmation', $infoArray, $user->email, $name, $subject);

            return Response::dataResponse(true, ['user' => $user, 'profile' => $profile],
                'Successfully registered');
        }

        Exceptions::nullException('Unable to create user');
    }

    /**
     * Registers user with oauth
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function oauthRegister(Request $request)
    {
        // Use for case insensitive check
        $oauth_type = strtolower($request['oauth_type']);

        $email = $request['email'];
        $user = User::where('email', '=', $email)->first();

        // Override the user with oauth account if user has not been confirmed
        if($user != null && !$this->emailConfirmed($user))
            $user->delete();

        // Creates a user with random password
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

            // Setting the appropriate oauth for the user
            $oauth = Oauth::create(['user_id' => $user->id]);
            if($oauth_type == 'facebook')
                $oauth->facebook = true;
            else if ($oauth_type == 'google')
                $oauth->google = true;

            return Response::dataResponse(true, [
                'user' => $user,
                'api_token' => $user->api_token,
                'profile' => $profile
            ], 'Successfully registered');
        }
    }


    /**
     * Checks if email is taken
     *
     * @param $email
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function emailTaken($email)
    {

        $user = User::where('email', '=', $email)->first();
        $confirmed = false;
        if($user != null && $user->confirmation_code == null)
            $confirmed = true;

        return $this->takenResponse($user, $confirmed);

    }

    /**
     * Checks if username is taken
     *
     * @param $username
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function usernameTaken($username)
    {

        $user = User::where('username', '=', $username)->first();
        $confirmed = false;
        if($user != null && $user->confirmation_code == null)
            $confirmed = true;

        return $this->takenResponse($user, $confirmed);

    }

    /**
     * Helper method returning a response for usernameTaken and emailTaken
     *
     * @param $user
     * @param $confirmed
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function takenResponse($user, $confirmed)
    {
        if($user == null) {
            return Response::dataResponse(true, ['taken' => false]);

        }
        else
            return Response::dataResponse(true, [
                'taken' => true,
                'confirmed' => $confirmed
            ]);
    }


    /**
     * Checks if user is confirmed
     *
     * @param $user
     * @return bool
     */
    public function emailConfirmed($user)
    {
        return $user->confirmation_code == null;
    }

    /**
     * Confirms user
     *
     * @param $confirmation_code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function confirm($confirmation_code)
    {

        if($confirmation_code == null)
            Exceptions::invalidConfirmationException();

        $user = User::where('confirmation_code', '=', $confirmation_code)->first();

        if($user == null)
            Exceptions::invalidConfirmationException();

        $user->confirmation_code = null;
        $user->api_token = $this->generateApiToken();
        // Token will expire in 1 year
        $user->token_expired_on = Carbon::now()->addDays(365);
        $user->update();

        return Response::successResponse('Account verified');

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
     * @return Array containing User and Profile
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

    /**
     * Creates a profile for a user
     *
     * @param Request $request
     * @param $user
     * @return mixed
     */
    public function createProfile(Request $request, $user)
    {
        $gender = $request->input('gender');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');

        if($request->has('birthday')) // Format birthday
            $birthday = \DateTime::createFromFormat('Y-m-d', $request->input('birthday'));
        else
            $birthday = null;

        // Stores image into local storage
        $image = ImageController::storeProfileImage($request->input('picture_url'));
        if($image != null)
            $image_id = $image->id;
        else
            $image_id = null;

        $profile = Profile::create([
            'user_id' => $user->id,
            'gender' => $gender,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'birthday' => $birthday,
            'image_id' => $image_id
        ]);

        return $profile;

    }

}
