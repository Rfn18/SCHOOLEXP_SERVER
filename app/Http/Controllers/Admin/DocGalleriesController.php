<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\DocGalleries as ModelsDocGalleries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocGalleriesController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'doc_category_id' => 'required|exists:doc_categories,id',
            'soft_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery gagal diambil.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $docGallery = ModelsDocGalleries::create([
            'event_id' => $request->event_id,
            'doc_category_id' => $request->doc_category_id,
            'soft_order' => $request->soft_order,
        ]);

        return new ApiResource(true, 'Data gallery berhasil ditambahkan.', $docGallery);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'doc_category_id' => 'required|exists:doc_categories,id',
            'soft_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery gagal diupdate.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $docGallery = ModelsDocGalleries::find($id);
        if (empty($docGallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }

        $docGallery->update([
            'event_id' => $request->event_id,
            'doc_category_id' => $request->doc_category_id,
            'soft_order' => $request->soft_order,
        ]);

        return new ApiResource(true, 'Data gallery berhasil diupdate.', $docGallery);
    }

    public function destroy(string $id)
    {
        $docGallery = ModelsDocGalleries::find($id);
        if (empty($docGallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }

        $docGallery->delete();

        return new ApiResource(true, 'Data gallery berhasil dihapus.', $docGallery);
    }

    public function reorder(Request $request): JsonResponse
    {
        $items = $request->validate([
            '*.id' => 'required|exists:doc_galleries,id',
            '*.soft_order' => 'required|integer|min:0',
        ]);

        foreach ($items as $item) {
            ModelsDocGalleries::where('id', $item['id'])->update(['soft_order' => $item['soft_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data gallery berhasil diurutkan ulang.',
        ]);
    }
}
