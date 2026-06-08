<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class notifications extends Model
{
    protected $table = 'notifications';
    
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'icon',
        'url',
        'sent_at',
        'is_read'
    ];

    protected $cast = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
