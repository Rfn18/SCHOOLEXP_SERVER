<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documentation extends Model
{
    use HasFactory;
    protected $table = 'documentations';

    protected $fillable = [
        'file_path',
        'event_id',
        'doc_category_id'
    ];

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function docCategory():BelongsTo
    {
        return $this->belongsTo(DocCategories::class);
    }
}
