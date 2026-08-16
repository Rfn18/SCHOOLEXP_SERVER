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

    public function show(string $id)
    {
        $docGallery = ModelsDocGalleries::with('docCategory', 'documentations')->find($id);

        if (empty($docGallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }

        return new ApiResource(true, 'Data gallery berhasil diambil.', $docGallery);
    }

    public function showByEventSlug($slug)
    {
        $docGallery = ModelsDocGalleries::with('docCategory', 'documentations')
            ->whereHas('event', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->get();

        if (empty($docGallery)) {
            return response()->json([
                'success' => false,
                'message' => 'Data gallery tidak ditemukan.',
            ], 404);
        }

        return new ApiResource(true, 'Data gallery berhasil diambil.', $docGallery);
    }
}
