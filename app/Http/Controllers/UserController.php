<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Contract\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use DateTime;

class UserController extends Controller
{
    use AuthenticatesUsers;
    public $createdUser;
    protected $auth;
    protected $redirectTo = RouteServiceProvider::HOME;
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    public function register(Request $request)
    {
        $userProperties = [
            'email' => $request->input('email'),
            'emailVerified' => false,
            'password' => $request->input('password'),
            'displayName' => $request->input('name'),
            'disabled' => false,
        ];
        try {
            $createdUser = $this->auth->createUser($userProperties);
            try {
                $verify = app('firebase.auth')->getUser($createdUser->uid)->emailVerified;
                $signInResult = $this->auth->signInWithEmailAndPassword($request['email'], $request['password']);
                if ($verify == 1) {
                    return view('welcome');
                }
                $email = app('firebase.auth')->getUser($createdUser->uid)->email;
                $link = app('firebase.auth')->sendEmailVerificationLink($email);
                //return response()->json('verify email sended');
            } catch (FirebaseException $e) {
                return response()->json(['Verify email failed' , $e]);
            }
            return $this->login($request);
        } catch (FirebaseException $e) {
            return response()->json(['Register failed' , $e]);
        }
    }
    public function login(Request $request)
    {
        try {
            $user= $this->auth->getUserByEmail($request->email);
            $updatedUser = $this->auth->enableUser($user->uid);
            //return $updatedUser->lastLoginAt;
            $signInResult = $this->auth->signInWithEmailAndPassword($request['email'], $request['password']);
            $customToken = $this->auth->createCustomToken($user->uid);
            //$signInWith = $this->auth->signInWithCustomToken($customToken);
            $customTokenString = $customToken->toString();
            /* return $customTokenString;//token olusturuldu,bu token ile authentication islemleri
            $signInResult = $this->auth->signInWithCustomToken($customToken);*/
            // return $updatedUser;
            Session::put('key', $customTokenString);
            $user = new User(($signInResult->data()));
            $time = new DateTime();
            $time = $time->format('Y-m-d-H-i-s');
            $lastlogin = $updatedUser->metadata->lastLoginAt;
            $lastlogin = $lastlogin->format('Y-m-d-H-i-s');
            if ($time > $lastlogin) {
                return view('welcome');
            } else {
                return response()->json(['msg', 'Session already login']);
            }

            //$result = Auth::login($user);

        } catch (FirebaseException $e) {
            return response()->json('Login failed', $e);
        }
    }
    public function logout(Request $request)
    {
        try {
            $updatedUser = app('firebase.auth')->enableUser($request->uid);
            $signInResult = $this->auth->signInWithEmailAndPassword($request['email'], $request['password']);
            Session::flush();
            return view('login');
        } catch (FirebaseException $e) {
            return response()->json('Logout failed' . $e);
        }
    }
    public function disableaccount(Request $request)
    {
        try {
            $updatedUser = app('firebase.auth')->disableUser($request->uid);
            Session::flush();
            return view('delete');
        } catch (FirebaseException $e) {
            return response()->json('Deletion failed' . $e);
        }
    }
    private function verifyemail(Request $request)
    {
        try {
            $verify = app('firebase.auth')->getUser($request->uid)->emailVerified;
            $signInResult = $this->auth->signInWithEmailAndPassword($request['email'], $request['password']);
            if ($verify == 1) {
                return view('welcome');
            }
            $email = app('firebase.auth')->getUser($request->uid)->email;
            $link = app('firebase.auth')->sendEmailVerificationLink($email);
            return response()->json('verify email sended');
        } catch (FirebaseException $e) {
            return response()->json('Verify email failed' . $e);
        }
    }
    public function resetPass(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        try {
            $email = $request->email;
            $link = app('firebase.auth')->sendPasswordResetLink($email);
            return response()->json(['message', 'An email has been sent. Please check your inbox.']);
        } catch (FirebaseException $e) {
            return response()->json('Operation failed' . $e);
        }
    }
}
