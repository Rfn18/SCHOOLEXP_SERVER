<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentationRequest;
use App\Models\DocGalleries;
use App\Models\Documentation;
use App\Services\CloudinaryService;
use App\Traits\ResolveEventFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentationController extends Controller
{
    use ResolveEventFolder;

    protected CloudinaryService $cloudinaryService;

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
            'data' => $documentation,
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
            'data' => $documentation,
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
            'data' => $documentation,
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
            '*.id' => 'required|exists:documentations,id',
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
            'data' => $createdDocumentations,
        ]);
    }

    /**
     * Set / toggle sebuah dokumentasi jadi highlight.
     * Max 2 highlight per gallery.
     */
    public function setHighlight(Request $request, Documentation $documentation): JsonResponse
    {
        $request->validate([
            'is_highlight' => ['required', 'boolean'],
        ]);

        $isHighlight = $request->boolean('is_highlight');

        if ($isHighlight) {
            $currentCount = Documentation::where('gallery_id', $documentation->gallery_id)
                ->where('is_highlight', true)
                ->where('id', '!=', $documentation->id)
                ->count();

            if ($currentCount >= 2) {
                throw ValidationException::withMessages([
                    'is_highlight' => 'Maksimal 2 foto highlight per gallery. Nonaktifkan salah satu highlight yang ada terlebih dahulu.',
                ]);
            }
        }

        $documentation->update(['is_highlight' => $isHighlight]);

        return response()->json([
            'success' => true,
            'message' => $isHighlight
                ? 'Documentation successfully marked as highlight'
                : 'Documentation highlight removed',
            'data' => [
                ...$documentation->fresh()->toArray(),
                'url' => $documentation->image_url,
            ],
        ]);
    }

    /**
     * Alternatif: replace langsung 2 highlight sekaligus (bulk set) dalam 1 gallery.
     * Cocok kalau UI admin-nya "pilih 2 foto lalu simpan".
     */
    public function replaceHighlights(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gallery_id' => ['required', 'exists:galleries,id'],
            'documentation_ids' => ['required', 'array', 'max:2'],
            'documentation_ids.*' => ['required', 'exists:documentations,id'],
        ]);

        // pastikan semua id yang dikirim benar-benar milik gallery ini
        $validCount = Documentation::where('gallery_id', $validated['gallery_id'])
            ->whereIn('id', $validated['documentation_ids'])
            ->count();

        if ($validCount !== count($validated['documentation_ids'])) {
            throw ValidationException::withMessages([
                'documentation_ids' => 'Salah satu dokumentasi tidak ditemukan pada gallery ini.',
            ]);
        }

        // reset semua highlight di gallery ini, lalu set ulang yang dipilih
        Documentation::where('gallery_id', $validated['gallery_id'])
            ->update(['is_highlight' => false]);

        Documentation::where('gallery_id', $validated['gallery_id'])
            ->whereIn('id', $validated['documentation_ids'])
            ->update(['is_highlight' => true]);

        $documentation = Documentation::where('gallery_id', $validated['gallery_id'])
            ->where('is_highlight', true)
            ->orderBy('soft_order')
            ->get()
            ->map(fn($doc) => [
                ...$doc->toArray(),
                'url' => $doc->image_url,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Highlighted documentation successfully updated',
            'data' => $documentation,
        ]);
    }
}