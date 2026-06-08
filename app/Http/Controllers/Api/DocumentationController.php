<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Documentation;
use App\Http\Requests\DocumentationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DocumentationController extends Controller
{
    public function index(): JsonResponse
    {
        $documentation = Documentation::with('galery')
                        ->when(request('gallery_id'), fn($q, $id) => $q->where('gallery_id', 'id'))
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
    $path = Storage::disk('cloudinary')->put(
        'documentations',
        $request->file('image')
    );

    $documentation = Documentation::create([
        'file_path' => $path,
        'alt_text' => $request->alt_text,
        'type' => $request->type,
        'gallery_id' => $request->gallery_id,
        'soft_order' => $request->soft_order,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Successfully created documentation',
        'data' => $documentation
    ]);
}

    public function show(Documentation $documentation): JsonResponse {
       return response()->json([
            ...$documentation->load('gallery')->toArray(),
            'url' => $documentation->image_url,
        ]);
    }

    public function update(DocumentationRequest $request, Documentation $documentation): JsonResponse
    {
        $publicId = $documentation->file_path;
        
        if ($request->hasFile('image')) {
            $publicId = $this->cloudinary->replace(
                $documentation->file_path,
                $request->file('image'),
                $request->type
            );
        }

        $documentation->update([
            'file_path' => $publicId,
            'alt_text' => $request->alt_text,
            'type' => $request->type,
            'gallery_id' => $request->gallery_id,
            'soft_order' => $request->soft_order ?? $documentation->soft_order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated documentation',
            'data' => $documentation
        ]);
    }

    public function destroy(Documentation $documentation): JsonResponse
    {
        Storage::disk('cloudinary')->delete($documentation->file_path);
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
}