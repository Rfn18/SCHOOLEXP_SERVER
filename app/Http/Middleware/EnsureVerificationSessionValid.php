<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerificationSessionValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard('api')->user();

        if (!$user || !$user->hasValidVerificationSession()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi kamu sudah kedaluwarsa. Silakan login ulang.',
            ], 403);
        }

        return $next($request);
    }
}