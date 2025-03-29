<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    public function getUser(Request $request){
        $user_name = $request->user_name;
        $users = User::where('name', 'like', $user_name.'%')
                        ->where('id', '!=', $request->user()->id)
                        ->get();

        return response()->json($users);
    }
}
