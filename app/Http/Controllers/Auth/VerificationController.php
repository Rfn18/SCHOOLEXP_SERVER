<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class VerificationController extends Controller
{
    protected int $verificationSessionDays = 1;

    public function verify(Request $request, $id, $hash)
    {
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $expectedSignature = hash_hmac(
            'sha256',
            "verification.verify|{$id}|{$hash}|{$expires}",
            config('app.key')
        );

        if (!hash_equals($expectedSignature, (string) $signature)) {
            return response()->json(['message' => 'Link verifikasi tidak valid'], 403);
        }

        if (now()->timestamp > (int) $expires) {
            return response()->json(['message' => 'Link verifikasi sudah kedaluwarsa'], 403);
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Link verifikasi tidak valid'], 403);
        }

        // First-time verification -> catat permanen
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Baik first-time maupun reverify (habis logout / idle 1 hari),
        // extend rolling session di sini.
        $user->update([
            'verification_expires_at' => now()->addDays($this->verificationSessionDays),
        ]);

        return response()->json(['message' => 'Email berhasil diverifikasi']);
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $key = 'verify-resend:' . $request->email;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return response()->json([
                'message' => 'Tunggu sebentar sebelum minta kirim ulang lagi'
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        RateLimiter::hit($key, 60);
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email verifikasi telah dikirim ulang']);
    }
}