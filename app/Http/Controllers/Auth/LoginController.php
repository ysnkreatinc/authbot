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


/*
TO SUBSCRIBE PAGES
https://www.facebook.com/dialog/oauth?response_type=token&display=popup&client_id=3077128572332597&redirect_uri=https%3A%2F%2Fdevelopers.facebook.com%2Ftools%2Fexplorer%2Fcallback&scope=pages_messaging


GIVING PERMISSION

https://www.facebook.com/v6.0/dialog/oauth?
  client_id=3077128572332597
  &redirect_uri=https://39cefc64.ngrok.io/done
  &scope=public_profile%2Cemail%2Cmanage_pages%2Cpages_messaging%2Cpages_messaging_subscriptions
  &state={state-param}


  PERMISSION
  

*/