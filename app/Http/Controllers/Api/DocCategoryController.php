<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\DocCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocCategoryController extends Controller
{
    public function index()
    {
        $docCategories = DocCategories::paginate(10);

        if ($docCategories->count() === 0) {
            return new ApiResource(true, "List doc category masih kosong", $docCategories);
        }

        return new ApiResource(true, "List data doc category", $docCategories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|alpha_dash|unique:doc_categories,slug|max:255",
            "name" => "required|string|max:255"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (is_numeric($request->name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori tidak boleh angka.',
                'data' => null
            ], 422);
        }

        if (DocCategories::where('name', $request->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori tidak boleh sama.',
                'data' => null
            ], 422);
        }

        $docCategory = DocCategories::create([
            "slug" => $request->slug,
            "name" => $request->name
        ]);

        return new ApiResource(true, "Successfully created doc category", $docCategory);
    }

    public function show($id)
    {
        $docCategory = DocCategories::findOrFail($id);

        return new ApiResource(true, "Detail doc category berdasarkan id", $docCategory);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|alpha_dash|unique:doc_categories,slug,".$id."|max:255",
            "name" => "sometimes|string|max:255"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->name && is_numeric($request->name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori tidak boleh angka.',
                'data' => null
            ], 422);
        }

        if ($request->name && DocCategories::where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama kategori tidak boleh sama.',
                'data' => null
            ], 422);
        }

        $docCategory = DocCategories::findOrFail($id);

        $docCategory->update([
            "slug" => $request->slug,
            "name" => $request->name ?? $docCategory->name
        ]);

        return new ApiResource(true, "Successfully updated doc category.", $docCategory);
    }

    public function destroy($id)
    {
        $docCategory = DocCategories::findOrFail($id);
        $docCategory->delete();

        return new ApiResource(true, "Successfully deleted doc category.", $docCategory);
    }
}