<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\EventCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
     public function index() {
        $eventcategories = EventCategories::paginate(10);
        if ($eventcategories->count() === 0) { 
            return new ApiResource(true, "List masih kosong", $eventcategories);
        }

        return new ApiResource(true, "List data jenis", $eventcategories);
    }
    
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "slug" => "required|alpha_dash|unique:event_categories,slug|max:255"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (is_numeric($request->nama_jenis)) {
           return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh angka.',
                'data' => null
            ], 422);
        }

        if (EventCategories::where('name', $request->name)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh sama.',
                'data' => null
            ], 422);
        }
        
        $eventcategories = EventCategories::create([
            "name" => $request->name,
            "slug" => $request->slug
        ]);

        return new ApiResource(true, "Successfully created jenis", $eventcategories);

    }

    public function show($id) {
        $eventcategories = EventCategories::findOrFail($id);
        return new ApiResource(true, "List data menu bedasaran id.", $eventcategories);
    }

    public function update(Request $request, $id) { 
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|string",
            "slug" => "required|alpha_dash|unique:event_categories,slug|max:255"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        if (EventCategories::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh sama.',
                'data' => null
            ], 422);
        }

        if (is_numeric($request->name)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama jenis tidak boleh angka.',
                'data' => null
            ], 422);
        }
        
        $eventcategories = EventCategories::findOrFail($id);
    
        $eventcategories->update([
            "id" => $eventcategories->id,
            "name" => $request->name,
            "slug" => $request->slug
        ]);

     return new ApiResource(true, "Successfully updated data.", $eventcategories);
    }

    public function destroy($id) {
        $eventcategories = EventCategories::findOrFail($id);
        
        $eventcategories->delete();

        return new ApiResource(true, "Successfully deleted data.", $eventcategories);
    }
}
