<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    public function postSignUp(Request $request) {
        $this->validate($request, [
            'email' => 'required|unique:users',
            'password' => 'required'
        ]);

        $email = $request['email'];
        $password = $request['password'];
        // TODO: should hash password

        $user = new User();
        $user->email = $email;
        $user->password = $password;

        $user->save();
        Auth::login($user);
        return redirect()->route('home');
    }

    public function postLogin(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        if( Auth::attempt(['email' => $request['email'],
                            'password' => $request['password'] ]) ) {
            return redirect()->route('home');
        }
        return redirect()->back()->with('login_failed');
    }

    public function getLogout() {
        Auth::logout();
        return redirect()->route('/login');
    }


}