<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocCategories extends Model
{
     protected $table = "doc_categories";

    protected $fillable = [
        'name',
        'slug'
    ];

    public function event(): HasMany {
        return $this->hasMany(Event::class);
    }

}
