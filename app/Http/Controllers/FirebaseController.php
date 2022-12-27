<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Storage as FirebaseStorage;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Auth;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Session;

class FirebaseController extends Controller
{
    public function __construct(Database $database, FirebaseStorage $storage, Auth $auth)
    {
        //$this->database = \App\Services\FirebaseService::connect();
        $this->database = $database;
        $this->tablename = 'userdatas';
        $this->storage = $storage;
        //$this->auth = $auth;
        //$this->middleware('auth');
    }
    /*protected function validator(array $data)
    {
        return Validator::make($data, [
            'uid' => ['required', 'string', 'unique'],
        ]);
    }*/
    public function index()
    {
        if (Session::get('key')) {
            return response()->json($this->database->getReference($this->tablename)->getValue());
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }
    public function store(Request $request)
    {
        if (Session::get('key')) {
            //$x=$request->except("name");
            $link=$this->update($request);
            $card = $request->all();
            $postRef = $this->database->getReference($this->tablename)->push($request->all());
            $postRef->push($link);
            if ($postRef) {
                $card = [
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'active' => $request->active,
                    'url'=>$link
                ];
                return $card;
            }
            //array('status', 'succesfully');
            else {
                return array('status', 'failed');
            }
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }
    public function show($keys)
    {
        if (Session::get('key')) {
            $temp = $this->database->getReference($this->tablename)->getValue();
            foreach ($temp as $t) {
                // return $t['email'];
                if ($t['email'] == $keys) {
                    return $t;
                    break;
                }
            }
            return $temp;
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }
    public function edit($keys, Request $request)
    {
        if (Session::get('key')) {
            $ref = $this->database->getReference($this->tablename);
            $temp = $ref->getValue()->first();
            foreach ($temp as $k => $t) {
                if ($t["email"] == $keys) {
                    //  return $t["name"]->set("sdfsf");
                    return    $this->database->getReference($this->tablename . "/" . $k)->update($request->all())->getValue();
                    //return $t;
                    break;
                }
            }
            return $temp;
            //$this->database->getReference($this->tablename)->getChild($keys)->getValue()->update();
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }
    public function update(Request $request)
    {
        if (Session::get('key')) {
            $image = $request->file('image');
            $firebase_storage_path = 'uploads/';
            if (isset($request->path))
                $firebase_storage_path .= $request->path . "/";
            $name     = $image->getClientOriginalName();
            $localfolder = public_path('firebase-temp-uploads') . '/';
            $extension = $image->getClientOriginalExtension();
            $file      = $name . '.' . $extension;
            if ($image->move($localfolder, $file)) {
                $uploadedfile = fopen($localfolder . $file, 'rb');
                app('firebase.storage')->getBucket()->upload($uploadedfile, ['name' => $firebase_storage_path . $name,]);
                //will remove from local laravel folder
                unlink($localfolder . $file);
                $url = urlencode($firebase_storage_path . $name);
                return $link="https://firebasestorage.googleapis.com/v0/b/fir-t-b9d8b.appspot.com/o/{$url}?alt=media";
                return 'success';
            } else {
                return 'error';
            }
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }

    /**
     * Remove the specified resource from storage.@env ('staging')

     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($keys)
    {
        if (Session::get('key')) {
            $deletion = $this->database
                ->getReference($this->tablename)->getChild($keys)
                ->remove();

            if ($deletion) {
                return response()->json('user has been deleted');
            } else {
                return response()->json('failed');
            }
        } else {
            return response()->json(['msg', 'Authenticate Error!!']);
        }
    }
}
