<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialLoginsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function google()
    {
        return Socialite::driver('google')->redirect();
    }
    public function googlecallback()
    {
        try {
            $google_user = Socialite::driver('google')->user();
            $user = User::where('google_id', $google_user->getId())->first();

            if (!$user) {
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                ]);

                Auth::login($new_user);

                return redirect()->intended('welcome');
            } else {
                Auth::login($user);

                return redirect()->intended('welcome');
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd('Something went wrong!' . $th->getMessage());
        }
    }
    public function facebook()
    {
        return Socialite::driver('facebook')->redirect();
    }
    public function facebookcallback()
    {
        try {
            $facebook_user = Socialite::driver('facebook')->user();
            $user = User::where('facebook_id', $facebook_user->getId())->first();

            if (!$user) {
                $new_user = User::create([
                    'name' => $facebook_user->getName(),
                    'email' => $facebook_user->getEmail(),
                    'facebook_id' => $facebook_user->getId(),
                ]);

                Auth::login($new_user);

                return redirect()->intended('welcome');
            } else {
                Auth::login($user);

                return redirect()->intended('welcome');
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd('Something went wrong!' . $th->getMessage());
        }
    }
    public function linkedin()
    {
        return Socialite::driver('linkedin')->redirect();
    }
    public function linkedincallback()
    {
        try {
            $linkedin_user = Socialite::driver('linkedin')->user();
            $user = User::where('linkedin_id', $linkedin_user->getId())->first();

            if (!$user) {
                $new_user = User::create([
                    'name' => $linkedin_user->getName(),
                    'email' => $linkedin_user->getEmail(),
                    'linkedin_id' => $linkedin_user->getId(),
                ]);

                Auth::login($new_user);

                return redirect()->intended('welcome');
            } else {
                Auth::login($user);

                return redirect()->intended('welcome');
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd('Something went wrong!' . $th->getMessage());
        }
    }
    public function twitter()
    {
        return Socialite::driver('twitter')->redirect();
    }
    public function twittercallback()
    {
        try {
            $twitter_user = Socialite::driver('twitter')->user();
            $user = User::where('twitter_id', $twitter_user->getId())->first();

            if (!$user) {
                $new_user = User::create([
                    'name' => $twitter_user->getName(),
                    'email' => $twitter_user->getEmail(),
                    'twitter_id' => $twitter_user->getId(),
                ]);

                Auth::login($new_user);

                return redirect()->intended('welcome');
            } else {
                Auth::login($user);

                return redirect()->intended('welcome');
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd('Something went wrong!' . $th->getMessage());
        }
    }
    public function redirectToInstagramProvider()
    {
        $appId = config('services.instagram.client_id');
        $redirectUri = urlencode(config('services.instagram.redirect'));
        return redirect()->to("https://api.instagram.com/oauth/authorize?app_id={$appId}&redirect_uri={$redirectUri}&scope=user_profile,user_media&response_type=code");
    }

    public function instagramProviderCallback(Request $request)
    {
        $code = $request->code;
        if (empty($code)) return redirect()->route('home')->with('error', 'Failed to login with Instagram.');

        $appId = config('services.instagram.client_id');
        $secret = config('services.instagram.client_secret');
        $redirectUri = config('services.instagram.redirect');

        $client = new Client();

        // Get access token
        $response = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => [
                'app_id' => $appId,
                'app_secret' => $secret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return redirect()->route('home')->with('error', 'Unauthorized login to Instagram.');
        }

        $content = $response->getBody()->getContents();
        $content = json_decode($content);

        $accessToken = $content->access_token;
        $userId = $content->user_id;

        // Get user info
        $response = $client->request('GET', "https://graph.instagram.com/me?fields=id,username,account_type&access_token={$accessToken}");

        $content = $response->getBody()->getContents();
        $oAuth = json_decode($content);

        // Get instagram user name
        $username = $oAuth->username;
    }
}
