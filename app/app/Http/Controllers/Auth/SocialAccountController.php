<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use Auth;
use User;
use SocialAccount;
class SocialAccountController extends Controller
{
    // 

    public function redirectToProvider($provider) {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider) {
        
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            Session::flash("Something wrong");
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        //return redirect($this->redirectTo);
        return redirect('/home');
    }

    public function findOrCreateUser($socialUser, $provider) {
        $account = SocialAccount::where('provider_name', $provider)->where('provider_id', $socialUser->getId())->first();
        if($account) {
            return $account->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first(); 
            if(!$user) {
                $user = User::create([
                    'email' => $socialUser->getEmail(),
                    'name' => $socialUser->getName()
                ]);
            }
            $user->accounts()->create([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId()
            ]);
            return $user;
        }
    }
}
