<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => app(CloudinaryService::class)->url($this->file_path)
        );
    }

    public function gallery()
    {
        return $this->belongsTo(DocGalleries::class);
    }

    
}
