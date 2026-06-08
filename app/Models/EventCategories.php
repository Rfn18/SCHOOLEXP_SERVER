<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategories extends Model
{
    protected $table = "event_categories";

    protected $fillable = [
        'name',
        'description'
    ];

    public function gallery():HasMany {
        return $this->hasMany(DocGalleries::class);
    }

}
