<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        $user = $user->get();

        if ($user->count()> 0){
            $response = [
                'status' => 'success',
                'data' => $user
            ];
        }else{
            $response = [
                'status' => 'no_data',
            ];
        }
        return $response;
    }

    public function detail($id){
        $user = User::find($id);
        if (!$user){
            $status = 'no_data';
        }else{
            $status = 'success';
        }
        $response = [
            'status' => $status,
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
                'status' => 'success',
                'data' => $user
            ];
        }else{
            $response = [
                'status' => 'error'
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
                    'status' => 'success',
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
                    'status' => 'success',
                    'data' => $user
                ];
            };
        }
        return $response;
    }

    public function delete(User $user){
        
        return 'Delete User ID :'.$user->id;
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
