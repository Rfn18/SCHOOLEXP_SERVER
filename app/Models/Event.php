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
        'cover_image',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'link',
        'status',
        'is_repeat',
        'user_id',
        'event_category_id'
    ];

    protected $cast = [
        'is_repeat' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',   
        'start_time' => 'time',
        'end_time' => 'time',
        'status' => 'enum',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function eventCategory():BelongsTo
    {
        return $this->belongsTo(EventCategories::class, "event_category_id");
    }
}
