<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Profile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Response;

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

    public function register(Request $request) {

        return $this->create($request);

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
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|alpha',
            'last_name' => 'required|alpha',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|AlphaNum|min:8|max:16|confirmed',
            'gender' => 'required'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  Request $request
     * @return User
     */
    protected function create(Request $request)
    {

        $data = $request->all();

        $validator = $this->validator($data);

        if ($validator->fails()){

            return $validator->errors()->all();

        }

        $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => str_random(60)
                ]);

        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->first_name = $data['first_name'];
        $profile->last_name = $data['last_name'];
        $profile->gender = $data['gender'];
        $profile->save();

        return $user;

    }

}
