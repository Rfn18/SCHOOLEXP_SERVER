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

    public function store(DocumentationRequest $request): JsonResponse
    {
        $file = $request->file('image');
        [$width, $height] = getimagesize($file->getRealPath());

        $folder = $this->resolveEventFolder($request->gallery_id);

        $path = $this->cloudinaryService->upload($file, $folder);

        $documentation = Documentation::create([
            'file_path' => $path,
            'alt_text' => $request->alt_text,
            'gallery_id' => $request->gallery_id,
            'soft_order' => $request->soft_order,
            'width' => $width,
            'height' => $height,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created documentation',
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

    public function update(DocumentationRequest $request, Documentation $documentation): JsonResponse
    {
        $publicId = $documentation->file_path;
        $width = $documentation->width;
        $height = $documentation->height;

        if ($request->hasFile('image')) {
            $this->cloudinaryService->delete($documentation->file_path);

            $file = $request->file('image');
            [$width, $height] = getimagesize($file->getRealPath());

            $folder = $this->resolveEventFolder($request->gallery_id ?? $documentation->gallery_id);

            $publicId = $this->cloudinaryService->upload($file, $folder);
        }

        $documentation->update([
            'file_path' => $publicId,
            'alt_text' => $request->alt_text,
            'gallery_id' => $request->gallery_id,
            'soft_order' => $request->soft_order ?? $documentation->soft_order,
            'width' => $width,
            'height' => $height,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated documentation',
            'data' => $documentation
        ]);
    }

    public function destroy(Documentation $documentation): JsonResponse
    {
        $this->cloudinaryService->delete($documentation->file_path);
        $documentation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted documentation',
        ]);
    }

    public function reorder(): JsonResponse
    {
        $items = request()->validate([
            '*.id'         => 'required|exists:documentations,id',
            '*.soft_order' => 'required|integer|min:0',
        ]);

        foreach ($items as $item) {
            Documentation::where('id', $item['id'])
                ->update(['soft_order' => $item['soft_order']]);
        }

        return response()->json(['message' => 'Urutan berhasil disimpan.']);
    }

    public function bulkDelete(): JsonResponse
    {
        $ids = request()->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:documentations,id',
        ])['ids'];

        $documentations = Documentation::whereIn('id', $ids)->get();

        foreach ($documentations as $documentation) {
            $this->cloudinaryService->delete($documentation->file_path);
            $documentation->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted documentations',
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $items = $request->validate([
            '*.id' => 'required|exists:documentations,id',
            '*.alt_text' => 'required|string',
            '*.gallery_id' => 'required|exists:doc_galleries,id',
            '*.soft_order' => 'required|integer|min:0',
        ]);

        foreach ($items as $item) {
            Documentation::where('id', $item['id'])
                ->update([
                    'alt_text' => $item['alt_text'],
                    'gallery_id' => $item['gallery_id'],
                    'soft_order' => $item['soft_order'],
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated documentations',
        ]);
    }

    public function bulkCreate(Request $request): JsonResponse
    {
        $items = $request->validate([
            '*.alt_text' => 'required|string',
            '*.gallery_id' => 'required|exists:doc_galleries,id',
            '*.soft_order' => 'required|integer|min:0',
            '*.image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5148',
        ]);

        $createdDocumentations = [];

        $galleryIds = array_unique(array_column($items, 'gallery_id'));
        $galleries = DocGalleries::with('event')->whereIn('id', $galleryIds)->get()->keyBy('id');

        foreach ($items as $item) {
            [$width, $height] = getimagesize($item['image']->getRealPath());

            $gallery = $galleries->get($item['gallery_id']);
            $eventSlug = $gallery?->event?->slug ?? 'unknown-event';
            $folder = 'documentations/' . Str::slug($eventSlug);

            $path = $this->cloudinaryService->upload($item['image'], $folder);

            $documentation = Documentation::create([
                'file_path' => $path,
                'alt_text' => $item['alt_text'],
                'gallery_id' => $item['gallery_id'],
                'soft_order' => $item['soft_order'],
                'width' => $width,
                'height' => $height,
            ]);

            $createdDocumentations[] = $documentation;
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully created documentations',
            'data' => $createdDocumentations
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