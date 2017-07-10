<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Profile;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
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

        $user = $this->create($request);
        if($user != null)
        {
            return response(json_encode([
                'success' => true,
                'data' => $user
            ]), 200);
        }

        return response(json_encode(['success' => false]), 200);

    }

    public function confirm($confirmation_code)
    {

        if( ! $confirmation_code)
            return response(json_encode(['errors' => ['Invalid confirmation code']]), 401);

        $user = User::where('confirmation_code', '=', $confirmation_code)->first();

        if($user == null)
            return response(json_encode(['errors' => ['Invalid confirmation code']]), 401);

        $user->confirmation_code = null;
        $user->update();

        return response(json_encode(['success' => 'Account verified']), 200);

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
    protected function create(Request $request)
    {

        $confirmation_code = str_random(30);
        $user = User::create([
                'email' => $request['email'],
                'username' => $request['username'],
                'password' => Hash::make($request['password']),
                'api_token' => $this->generateApiToken(),
                'confirmation_code' => $confirmation_code
                ]);

        if($user == null)
            return null;

        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->gender = $request['gender'];
        $profile->save();

        Mail::send('confirmation', ['confirmation_code' => $confirmation_code]
                    , function($message) use ($request) {
            $message->to($request['email'], $request['username'])
                ->subject('Verify your email address');
        });

        return $user;

    }

}
