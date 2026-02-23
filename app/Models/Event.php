<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;
    protected $table = 'events';

    protected $fillable = [
        'slug',
        'title',
        'description',
        'location',
        'poster',
        'date',
        'status',
        'is_all_day',
        'user_id',
        'event_category_id'
    ];

    protected $casts = [
        'is_all_day' => 'boolean',
        'date' => 'date',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function eventCategory():BelongsTo
    {
        return $this->belongsTo(EventCategories::class, "event_category_id");
    }

    public function documentation(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }
}
