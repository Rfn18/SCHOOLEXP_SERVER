<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\DocGalleries as ModelsDocGalleries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocGalleries extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $galleries = ModelsDocGalleries::with(['docCategory', 'documentations'])
            ->when(request('event_id'), fn($q, $id) => $q->where('event_id', $id))
            ->orderBy('soft_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Successfully retrieved galleries',
            'data' => $galleries,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'doc_category_id' => 'required|exists:doc_categories,id',
            'soft_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery gagal diambil.',
                'errors' => $validator->errors()
            ], 400);
        }

        $doc_gallery = ModelsDocGalleries::create([
            'event_id' => $request->event_id,
            'doc_category_id' => $request->doc_category_id,
            'soft_order' => $request->soft_order
        ]);

        return new ApiResource(true, 'Data gallery berhasil ditambahkan.', $doc_gallery);
   
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doc_gallery = ModelsDocGalleries::with('docCategory')->find($id);

        if (empty($doc_gallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }

        return new ApiResource(true, 'Data gallery berhasil diambil.', $doc_gallery);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'doc_category_id' => 'required|exists:doc_categories,id',
            'soft_order' => 'required|integer|min:0'
        ]);

        if ($validator->errors()) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery gagal diupdate.',
                'errors' => $validator->errors()
            ], 400);
        }

        $doc_gallery = ModelsDocGalleries::find($id);
        if (empty($doc_gallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }
        
        $doc_gallery->update([
            'event_id' => $request->event_id,
            'doc_category_id' => $request->doc_category_id,
            'soft_order' => $request->soft_order
        ]);

        return new ApiResource(true, 'Data gallery berhasil diupdate.', $doc_gallery);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doc_gallery = ModelsDocGalleries::find($id);
        if (empty($doc_gallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }
        
        $doc_gallery->delete();
        return new ApiResource(true, 'Data gallery berhasil dihapus.', $doc_gallery);
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
