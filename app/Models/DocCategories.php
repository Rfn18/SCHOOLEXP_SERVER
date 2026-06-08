<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocCategories extends Model
{
     protected $table = "doc_categories";

    protected $fillable = [
        'name',
        'description'
    ];

    public function gallery(): HasMany {
        return $this->hasMany(DocGalleries::class);
    }

}
