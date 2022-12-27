<?php

use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(FirebaseController::class)->middleware('login')->group(function(){
    Route::get('/index','index');
    Route::get('/show/{keys}','show');
    Route::post('/store','store');
    Route::post('/edit/{keys}','edit');
    Route::post('/update','update');
    Route::delete('/destroy/{keys}','destroy');
});

Route::post('login',[UserController::class,'login']);
Route::post('logout',[UserController::class,'logout']);
Route::post('register',[UserController::class,'register']);
Route::post('disableaccount',[UserController::class,'disableaccount']);
Route::post('verifyemail',[UserController::class,'verifyemail']);
Route::post('resetPass',[UserController::class,'resetPass']);

