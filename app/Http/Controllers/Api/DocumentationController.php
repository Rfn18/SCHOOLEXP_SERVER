<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DocumentationController extends Controller
{
    public function index()
    {
        $documentations = Documentation::with(['event', 'docCategory'])
            ->paginate(10);

        if ($documentations->count() === 0) {
            return new ApiResource(true, "List documentation masih kosong", $documentations);
        }

        return new ApiResource(true, "List data documentation", $documentations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "file_path" => "required|string",
            "event_id" => "required|exists:events,id",
            "doc_category_id" => "required|exists:doc_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $documentation = Documentation::create([
            "file_path" => $request->file_path,
            "event_id" => $request->event_id,
            "doc_category_id" => $request->doc_category_id
        ]);

        return new ApiResource(true, "Successfully created documentation", $documentation);
    }

    public function show($id)
    {
        $documentation = Documentation::with(['event', 'docCategory'])
            ->findOrFail($id);

        return new ApiResource(true, "Detail documentation berdasarkan id", $documentation);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "file_path" => "sometimes|string",
            "event_id" => "sometimes|exists:events,id",
            "doc_category_id" => "sometimes|exists:doc_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $documentation = Documentation::findOrFail($id);

        $documentation->update([
            "file_path" => $request->file_path,
            "event_id" => $request->event_id ?? $documentation->event_id,
            "doc_category_id" => $request->doc_category_id ?? $documentation->doc_category_id
        ]);

        return new ApiResource(true, "Successfully updated documentation.", $documentation);
    }

    public function destroy($id)
    {
        $documentation = Documentation::findOrFail($id);

        if ($documentation->file_path &&
            Storage::disk('public')->exists($documentation->file_path)) {

            Storage::disk('public')->delete($documentation->file_path);
        }

        $documentation->delete();

        return new ApiResource(true, "Successfully deleted documentation.", $documentation);
    }
}