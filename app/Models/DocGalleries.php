<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocGalleries extends Model
{
    protected $table = "doc_galleries";
    protected $fillable = [
        'event_id',
        'doc_category_id',
        'soft_order'
    ];      

    public function event():BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function docCategory():BelongsTo
    {
        return $this->belongsTo(DocCategories::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class, 'gallery_id');
    }
}
