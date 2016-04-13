<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use App\UserLocation;
use Davibennun\LaravelPushNotification;

class TestController extends Controller
{
    
    // GET
    public function index() {
        $users = User::all();
        return response()->json($users);
    }

    // POST
//    public function store() {
//        $user = new User();
//        $note->body = Input::get('body', 'empty note');
//        $note->save();
//
//        return Response::json($note);
//    }

    // GET
    public function show($id) {
        $user = User::find($id);
        return $user;
    }

//    // PUT
//    public function update($id) {
//        $note = Note::find($id);
//        if(Input::has('body')) {
//            $note->body = Input::get('body', 'empty note');
//            $note->save();
//            return Response::json(['note'=>$note, 'message'=>'Note Updated']);
//        }
//        return Response::json(['note'=>$note, 'message'=>'No Body Sent']);
//    }
//
//    // DELETE 
//    public function destroy($id) {
//        $note = Note::find($id);
//        $note->delete();
//        return Response::json($note);
//    }



}
