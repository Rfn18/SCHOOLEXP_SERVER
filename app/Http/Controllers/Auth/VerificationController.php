<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Validasi signature
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

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah terverifikasi sebelumnya']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email berhasil diverifikasi']);
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email sudah terverifikasi']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email verifikasi telah dikirim ulang']);
    }
}