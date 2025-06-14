<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @param Request $request     *
     */
    public function login(Request $request) {

        $user_type = $request->userType ?? 'employee';
        $user = null;

        if ($user_type === 'employee'){
            if (!Auth::guard('employee')->attempt([
                'email_work' => $request->email,
                'password' => $request->password
            ])){
                return response(['message' => 'Invalid Credentials', 'type' => $user_type], Response::HTTP_UNAUTHORIZED);
            }
        }elseif($user_type === 'agent'){
            $hashed = Hash::make($request->password);

            if (!Auth::guard('agent')->attempt([
                'email_work' => $request->email,
                'password' => $request->password,
                // 'agent_id' => function($query){
                //     $query->where('agent_id', 4);
                // }
            ])){
                return response(['message' => 'Invalid Credentials', 'type' => $user_type], Response::HTTP_UNAUTHORIZED);
            }
        }

        $user = Auth::guard($request->userType ?? 'employee')->user()->load('user_code');

        $token = $user->createToken('token')->plainTextToken;

        $cookie = cookie('jwt_tms', $token, 60*24*365);

        return response([
            'user' => $user
        ])->cookie($cookie);
    }

    public function user(){
        return Auth::user()->load('user_code');
    }

    public function logout(){
        $cookie = Cookie::forget('jwt_tms');

        return response([
            'message' => 'Success'
        ])->cookie($cookie);
    }

    public function generatePass(Request $request){
        $myPass = $request->pass ?? '';

        $newPassword = $this->random_str();

        if ($myPass === ''){
            $hashed = Hash::make($newPassword);
        }else{
            $hashed = Hash::make($myPass);
        }

        return response()->json(['result' => 'OK', 'newpass' => $myPass === '' ? $newPassword : $myPass, 'hashed' => $hashed]);
    }

    public function checkPass(Request $request){
        $pass = $request->pass ?? '';
        $hashed = $request->hashed ?? '';

        return response()->json(['result' => Hash::check($pass, $hashed)]);
    }

    function random_str(
        int $length = 10,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
