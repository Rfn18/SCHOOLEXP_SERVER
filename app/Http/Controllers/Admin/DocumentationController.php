<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DocumentationController extends Controller
{
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