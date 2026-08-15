<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function login(Request $request) {
         $validator = Validator::make($request->all(), [
            'email' => "required|string",
            'password' => 'required|min:8',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        };
        
        $credentials = $request->only('email', 'password');

        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        $user = auth()->guard('api')->user()->load('role');

        if (!$user->hasVerifiedEmail()) {
            Auth::guard('api')->logout();
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek email kamu.',
            ], 403);
        }

        return new ApiResource(true, 'Login Berhasil', [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function me() {
        $user = auth()->guard('api')->user()->load('role');
        return new ApiResource(true, 'User Data', $user);
    }

    public function refresh() {
        return $this->respondWithToken(auth()->guard('api')->refresh());
    }

    protected function respondWithToken($token) {
        return new ApiResource(true, 'Token Retrieved Successfully', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout() {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        auth()->guard('api')->logout();

        if($removeToken) {
            return new ApiResource(true, 'Logout Berhasil', auth()->guard('api')->user());
        }
    }
}
