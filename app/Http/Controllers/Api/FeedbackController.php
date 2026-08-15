<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackRateLimit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
  public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'min:10', 'max:500'],   
            'name' => ['nullable', 'string', 'max:100'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min' => 'Pesan minimal 10 karakter.',
            'message.max' => 'Pesan maksimal 500 karakter.',
        ]);

        $cooldownSeconds = 60;
        $now = Carbon::now();
        
        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'unknown';
        $salt = config('app.key');
        
        $fingerprintHash = hash('sha256', $ip . '|' . $userAgent . '|' . $salt);

        $isRateLimited = DB::transaction(function () use ($fingerprintHash, $cooldownSeconds, $now) {
            
            $rateLimit = FeedbackRateLimit::where('fingerprint_hash', $fingerprintHash)
                ->lockForUpdate()
                ->first();

            if ($rateLimit) {
                // Cek apakah masih dalam masa cooldown
                $cooldownEnd = $rateLimit->last_submit_at->copy()->addSeconds($cooldownSeconds);
                
                if ($now->lessThan($cooldownEnd)) {
                    return true;
                }

                $rateLimit->update([
                    'last_submit_at' => $now,
                    'submit_count' => $rateLimit->submit_count + 1,
                    'expires_at' => $now->copy()->addDay(),
                ]);
            } else {
                FeedbackRateLimit::create([
                    'fingerprint_hash' => $fingerprintHash,
                    'last_submit_at' => $now,
                    'submit_count' => 1,
                    'expires_at' => $now->copy()->addDay(),
                ]);
            }

            return false;
        });

        if ($isRateLimited) {
            return response()->json([
                'message' => 'Anda mengirim feedback terlalu cepat. Silakan tunggu beberapa saat lagi.'
            ], 429);
        }

        $validated['category'] = $validated['category'] ?? 'general';
        $validated['message'] = trim($validated['message']);
        $validated['name'] = $validated['name'] ?? null;
        $validated['anonymous_code'] = Feedback::generateUniqueAnonymousCode();

        $feedback = Feedback::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feedback berhasil dikirim.',
            'anonymous_code' => $feedback->anonymous_code,
        ], 201);
    }
}