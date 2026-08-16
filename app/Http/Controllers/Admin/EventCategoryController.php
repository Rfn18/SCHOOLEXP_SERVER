<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\EventCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (is_numeric($request->name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh angka.',
                'data' => null,
            ], 422);
        }

        if (EventCategories::where('name', $request->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh sama.',
                'data' => null,
            ], 422);
        }

        $eventCategory = EventCategories::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return new ApiResource(true, 'Successfully created jenis', $eventCategory);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->name && EventCategories::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh sama.',
                'data' => null,
            ], 422);
        }

        if ($request->name && is_numeric($request->name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh angka.',
                'data' => null,
            ], 422);
        }

        $eventCategory = EventCategories::findOrFail($id);

        $eventCategory->update([
            'name' => $request->name ?? $eventCategory->name,
            'description' => $request->description ?? $eventCategory->description,
        ]);

        return new ApiResource(true, 'Successfully updated data.', $eventCategory);
    }

    public function destroy($id)
    {
        $eventCategory = EventCategories::findOrFail($id);
        $eventCategory->delete();

        return new ApiResource(true, 'Successfully deleted data.', $eventCategory);
    }
}
