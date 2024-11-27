<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        // $email = $request->email;
        // $password = $request->password;
        $checkLogin =  Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);
        if ($checkLogin){
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $response = [
                'status' => 200,
                'token' =>  $token
            ];
        }else{
            $response = [
                'status' => 401,
                'title' => 'Unauthorized',
            ];
        }
        return $response;
    }
}
