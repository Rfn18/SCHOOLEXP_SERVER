<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    use HasFactory;
    protected $table = 'documentations';

    protected $fillable = [
        'file_path',
        'alt_text',
        'width',
        'height',
        'gallery_id',
        'soft_order'
    ];

    protected $cast = [
        'soft_order' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function gallery()
    {
        return $this->belongsTo(DocGalleries::class);
    }

    
}
