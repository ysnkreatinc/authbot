<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;
use App\User;
use Socialite;
use Auth;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }


    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->scopes([
            'public_profile', 'manage_pages', 'pages_messaging', 'pages_messaging_subscriptions'
        ])->redirect();
    }


    public function handleProviderCallback()
    {
        $getInfo = Socialite::driver('facebook')->stateless()->user();
        Session::put('access_token', $getInfo->token);
        $user = $this->createUser($getInfo,'facebook'); 
        auth()->login($user); 
        return redirect()->route('listpages');
        // $user->token;
    }


    function createUser($getInfo,$provider){
        $user = User::where('provider_id', $getInfo->id)->first();
        if (!$user) {
             $user = User::create([
                'name'     => $getInfo->name,
                'email'    => $getInfo->email,
                'provider' => $provider,
                'provider_id' => $getInfo->id
            ]);
          }
          return $user;
    }

}
