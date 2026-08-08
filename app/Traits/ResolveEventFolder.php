<?php

namespace App\Traits;

use App\Models\DocGalleries;
use Illuminate\Support\Str;

trait ResolveEventFolder
{
    public function resolveEventFolder(int $galleryId): string
    {
        $gallery = DocGalleries::with('event')->find($galleryId);
        $eventSlug = $gallery?->event?->slug;

        $folderName = $eventSlug ? Str::slug($eventSlug) : 'unknown-event';

        return "documentations/{$folderName}";
    }
}
