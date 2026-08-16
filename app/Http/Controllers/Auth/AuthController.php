<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Berapa lama sesi terverifikasi berlaku sebelum harus reverify lagi.
     * Ubah di satu tempat ini aja kalau mau ganti durasi.
     */
    protected int $verificationSessionDays = 1;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => "required|string",
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        $user = auth()->guard('api')->user()->load('role');

        if (!$user->hasVerifiedEmail()) {
            auth()->guard('api')->logout();
            $this->sendVerificationWithThrottle($user);

            return response()->json([
                'success' => false,
                'message' => 'Email belum diverifikasi. Link verifikasi sudah dikirim ke email kamu.',
            ], 403);
        }

        if (!$user->hasValidVerificationSession()) {
            auth()->guard('api')->logout();
            $this->sendVerificationWithThrottle($user);

            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi kamu sudah kedaluwarsa. Link verifikasi baru sudah dikirim ke email kamu.',
            ], 403);
        }

        $user->update([
            'verification_expires_at' => now()->addDays($this->verificationSessionDays),
        ]);

          $ttlMinutes = auth()->guard('api')->factory()->getTTL();
 
        $tokenCookie = cookie(
            name: 'access_token',
            value: $token,
            minutes: $ttlMinutes,
            path: '/',
            domain: null,
            secure: true,       // WAJIB true kalau production (HTTPS only)
            httpOnly: true,      // kunci utama, JS gak bisa akses
            raw: false,
            sameSite: 'Lax',  
        );

         $csrfToken = Str::random(40);
        $csrfCookie = cookie(
            name: 'XSRF-TOKEN',
            value: $csrfToken,
            minutes: $ttlMinutes,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: false,     // ini yang beda, sengaja bisa dibaca JS
            raw: false,
            sameSite: 'Lax',
        );

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'data' => [
                'user' => $user,
            ],
        ])->withCookie($tokenCookie)->withCookie($csrfCookie);
    }

    public function validateCsrf(Request $request)
    {
        $cookieToken = $request->cookie('XSRF-TOKEN');
        $headerToken = $request->header('X-XSRF-TOKEN');
    
        if (!$cookieToken || !$headerToken || !hash_equals($cookieToken, $headerToken)) {
            abort(419, 'CSRF token tidak valid');
        }
    }
 

    public function me()
    {
        $user = auth()->guard('api')->user()->load('role');
        return new ApiResource(true, 'User Data', $user);
    }

    public function refresh()
    {
        try {
            $user = auth()->guard('api')->user();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        if (!$user->hasValidVerificationSession()) {
            auth()->guard('api')->logout();

            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi kamu sudah kedaluwarsa. Silakan login ulang.',
            ], 403);
        }

        $user->update([
            'verification_expires_at' => now()->addDays($this->verificationSessionDays),
        ]);

        return $this->respondWithToken(auth()->guard('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return new ApiResource(true, 'Token Retrieved Successfully', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout(Request $request)
    {
        $user = auth()->guard('api')->user();
    
        if ($user) {
            $user->update(['verification_expires_at' => null]);
        }
      $token = $request->bearerToken() ?? $request->cookie('access_token');
    
        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (JWTException $e) {
                $this->sendVerificationWithThrottle($user);
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid atau sudah kedaluwarsa.',
                ], 401);
            }
        }
    
        auth()->guard('api')->logout();
    
        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil',
        ])
            ->withCookie(cookie()->forget('access_token'))
            ->withCookie(cookie()->forget('XSRF-TOKEN'));
    }

    protected function sendVerificationWithThrottle(User $user): void
    {
        $key = 'verify-resend:' . $user->email;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return; 
        }

        RateLimiter::hit($key, 60); 
        $user->sendEmailVerificationNotification();
    }
}