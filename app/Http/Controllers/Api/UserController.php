<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    public function index(Request $request){

        $where = [];
        if ($request->name){
            $where[] = ['name','like','%'.$request->name.'%'];
        }
        if ($request->email){
            $where[] = ['email','like','%'.$request->email.'%'];
        }
        $user = User::orderBy('id','desc');
        if (!empty($where)){
            $user = $user->where($where);
        }
        $user = $user->with('post')->paginate();
        if ($user->count()> 0){
            $statusCode = 200;
            $statusText = 'success';
        }else{
            $statusCode = 204;
            $statusText = 'no_data';
        }
        $user = new UserCollection($user, $statusText, $statusCode);
        return $user;
    }

    public function detail($id){
        $user = User::with('post')->find($id);
        if (!$user){
            $statusCode = 404;
            $statusText = 'Not Found';
        }else{
            $statusCode = 200;
            $statusText = 'success';
            $user = new UserResource($user);
        }
        $response = [
            'status' => $statusCode,
            'title' => $statusText,
            'data' => $user
        ];
        return $response;
    }

    public function create(Request $request){
        $this->validation($request);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($user->id){
            $response = [
                'status' => 200,
                'title' => 'success',
                'data' => $user
            ];
        }else{
            $response = [
                'status' => 500,
                'title' => 'Server Error',
            ];
        }
        return $response;
    }

    public function update(Request $request,$id){
        $user = User::find($id);
        if (!$user){
            $response = [
                'status' => 'no_data'
            ];
        }else{
            $this->validation($request, $id);
            $method = $request->method();
            if($method == 'PUT'){
                // $userRequest = $request->all();
                $user->name = $request->name;
                $user->email = $request->email;
                if (!empty($request->password)){
                    $user->password = Hash::make($request->password);
                }else{
                    $user->password = null;
                }
                $user->save();
                $response = [
                    'status' => 200,
                    'title' => 'success',
                    'data' => $user
                ];
            }else{
                if ($request->name){
                    $user->name = $request->name;
                }
                if ($request->email){
                    $user->email = $request->email;
                }
                if (!empty($request->password)){
                    $user->password = Hash::make($request->password);
                }else{
                    $user->password = null;
                }
                $user->save();
                $response = [
                    'status' => 200,
                    'title' => 'success',
                    'data' => $user
                ];
            };
        }
        return $response;
    }

    public function delete($id){
        $user = User::find($id);
        if ($user){
            $status = User::destroy($user->id);
            if (!$status){
                $response = [
                    'status' => 500,
                    'title' => 'Server Error',
                ];
            }else{
                $response = [
                    'status' => 200,
                    'title' => 'success',
                ];
            }
        }else{
            $response = [
                'status' => 'no_data'
            ];
        }
        return $response;
    }

    public function validation($request, $id = 0){
        $emailValidation = 'required|email|unique:users';
        if (!empty($id)){
            $emailValidation.= ',email,'.$id;
        }
        $rules = [
            'name' => 'required|min:5',
            'email' => $emailValidation,
            'password' => 'required|min:8'
        ];
        $messages = [
            'name.required' => 'Họ tên buộc phải nhập',
            'name.min' => 'Họ tên không nhỏ hơn :min ký tự',
            'email.required' => 'Email buộc phải nhập',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email này đã tồn tại',
            'password.required' => 'Mật khẩu buộc phải nhập',
            'password.min' => 'Mật khẩu không nhỏ hơn :min ký tự'
        ]; 
        $request->validate($rules, $messages);
    }
}
