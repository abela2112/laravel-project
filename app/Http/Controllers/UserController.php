<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    //

    public static function create()
    {
        return view('users.register');
    }
    public static function store(Request $request)
    {
        $formFields = $request->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:6',
        ]);
        $formFields['password'] = bcrypt($formFields['password']);
        $user = User::create($formFields);

        //login user
        auth()->login($user);

        return redirect('/')->with('message', 'User has been created successfully');
    }
    //logout user
    public static function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'you have been logged out!');
    }

    public static function login()
    {
        return view('users.login');
    }

    public static function authenticate(Request $request)
    {
        $formFields = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required'
        ]);

        if(auth()->attempt($formFields)){
            $request->session()->regenerate();
            return redirect('/')->with('message', 'you are now logged in!');
        }

        return back()->with('message','invalid credentials')->onlyInput('email');
    }
}
