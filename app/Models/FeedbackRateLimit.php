<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackRateLimit extends Model
{
    protected $table = 'feedback_rate_limits';

    protected $fillable = [
        'fingerprint_hash',
        'last_submit_at',
        'submit_count',
        'expires_at',
    ];

    protected $casts = [
        'last_submit_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}