<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;

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
            
            // $token = $user->createToken('auth_token')->plainTextToken;
            // $tokenResult = $user->createToken('auth_api');
            
            // /** Thiết lập expires */
            // $token = $tokenResult->token;
            // $token->expires_at = Carbon::now()->addMinutes(60);
            
            // /** Trả về Access Token */
            // $accessToken = $tokenResult->accessToken;

            // /** Lấy về expires*/
            // $expires = Carbon::parse($token->expires_at)->toDateTimeString();

            $client = Client::where('password_client',1)->first();
            if ($client){
                $clientSecret = $client->secret;
                $clientId = $client->id;
                $response = Http::asForm()->post('http://127.0.0.1:8001/oauth/token', [
                    'grant_type' => 'password',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'username' => $request->email,
                    'password' => $request->password,
                    'scope' => '',
                ]);
                return $response;
            }

            // $response = [
            //     'status' => 200,
            //     'token' =>  $accessToken,
            //     'expires' => $expires,
            // ];

        }else{
            $response = [
                'status' => 401,
                'title' => 'Unauthorized',
            ];
        }
        return $response;
    }

    public function logout(){
        $user = Auth::user();
        $status = $user->token()->revoke();
        $response = [
            'status' => 200,
            'title' => 'Logout',
        ];
        return $response;
    }

    public function getToken(Request $request){
        // $user = User::find(1);
        // dd($user);

        /** Delete Token All */
        // $user->tokens()->delete();

        /** Delete Token by ID User */
        // $user->tokens()->where('id', 1)->delete();

        /** Delete Token Current User */
        return $request->user()->currentAccessToken()->delete();
    }

    public function refreshToken(Request $request){
        if ($request->header('authorization')){
            $hashToken = $request->header('authorization');
            $hashToken = str_replace('Bearer','',$hashToken);
            $hashToken = trim($hashToken);
            $token = PersonalAccessToken::findToken($hashToken);
            if ($token){
                $tokenCreated = $token->created_at;
                $expire = Carbon::parse($tokenCreated)->addMinutes(config('sanctum.expiration'));
                if (Carbon::now() >= $expire){
                    $userId = $token->tokenable_id;
                    $user = User::find($userId);
                    $user->tokens()->delete();
                    $newToken = $user->createToken('auth_token')->plainTextToken;
                    $response = [
                        'status' => 200,
                        'token' =>  $newToken
                    ];
                }else{
                    $response = [
                        'status' => 200,
                        'title' => 'Unexpired',
                    ];
                }
            }else{
                $response = [
                    'status' => 401,
                    'title' => 'Unauthorized',
                ];
            }
        }else{
            $response = [
                'status' => 401,
                'title' => 'Unauthorized',
            ];
        }
        return $response;
    }
}
