<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAuthController extends Controller
{
      public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:8',
        'role_id' => 'required|exists:roles,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'is_active' => $request->is_active ?? true,
        'role_id' => $request->role_id,
        'password' => Hash::make($request->password),
    ]);

    return response()->json([   
        'message' => 'User created successfully',
        'user' => $user
    ], 201);
}

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

        return new ApiResource(true, 'Login Berhasil', [
            'user' => auth()->guard('api')->user(),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function me() {
        return new ApiResource(true, 'User Data',auth()->guard('api')->user());
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

        if($removeToken) {
            return new ApiResource(true, 'Logout Berhasil', auth()->guard('api')->user());
        }
    }
}
