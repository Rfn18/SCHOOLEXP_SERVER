<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\Documentation;
use App\Http\Requests\DocumentationRequest;
use App\Models\DocGalleries;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use App\Traits\ResolveEventFolder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    use ResolveEventFolder;
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(): JsonResponse
    {
        $documentation = Documentation::with('gallery')
                        ->when(request('gallery_id'), fn($q, $id) => $q->where('gallery_id', $id))
                        ->orderBy('soft_order')
                        ->get()
                        ->map(fn($doc) => [
                            ...$doc->toArray(),
                            'url' => $doc->image_url,
                        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved documentation',
            'data' => $documentation
        ]);
    }

    public function show(Documentation $documentation): JsonResponse
    {
        return response()->json([
            ...$documentation->load('gallery')->toArray(),
            'url' => $documentation->image_url,
        ]);
    }

    public function topByCategory(): JsonResponse
    {
        $eventId = request('event_id');
        $galleryId = request('gallery_id');

        $documentation = Documentation::with(['gallery.docCategory'])
            ->whereHas('gallery', function ($q) use ($eventId, $galleryId) {
                $q->when($eventId, fn($q2) => $q2->where('event_id', $eventId));
                $q->when($galleryId, fn($q2) => $q2->where('id', $galleryId));
            })
            ->orderBy('soft_order')
            ->get()
            ->values()
            ->map(fn($doc) => [
                ...$doc->toArray(),
                'category' => $doc->gallery->docCategory,
                'url' => $doc->image_url,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved top documentation per category',
            'data' => $documentation,
        ]);
    }

   public function highlight(): JsonResponse
    {
        $query = Documentation::with('gallery')
            ->when(request('gallery_id'), fn($q, $id) => $q->where('gallery_id', $id));

        // 1. Coba ambil yang sudah di-set manual oleh admin
        $documentation = (clone $query)
            ->where('is_highlight', true)
            ->orderBy('soft_order')
            ->limit(2)
            ->get();

        // 2. Kalau admin belum set sama sekali, fallback ke "2 event terbaru"
        if ($documentation->isEmpty()) {
            $documentation = (clone $query)
                ->orderByDesc('created_at')
                ->limit(2)
                ->get();
        }

        $documentation = $documentation->map(fn($doc) => [
            ...$doc->toArray(),
            'url' => $doc->image_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved highlighted documentation',
            'data' => $documentation->values(),
        ]);
    }
}