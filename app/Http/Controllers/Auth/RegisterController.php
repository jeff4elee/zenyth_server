<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Profile;
use App\Http\Controllers\Controller;
use App\Http\Requests\DataValidator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

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

    public function register(Request $request)
    {

        $validator = DataValidator::validateRegister($request);
        if($validator->fails())
            return response(json_encode([
                'errors' => $validator->errors()->all()
            ]), 400);

        $user = $this->create($request);
        if($user != null)
        {
            return response(json_encode(['register' => true]), 201);
        }

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

        $user = User::create([
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'api_token' => "Bearer " . str_random(60)
                ]);

        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->first_name = $request['first_name'];
        $profile->last_name = $request['last_name'];
        $profile->gender = $request['gender'];
        $profile->save();

        return $user;

    }

}
