<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['user', 'eventCategory'])->paginate(10);

        if ($events->count() === 0) {
            return new ApiResource(true, "List event masih kosong", $events);
        }

        return new ApiResource(true, "List data event", $events);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|alpha_dash|unique:events,slug|max:255",
            "title" => "required|string|max:255",
            "description" => "required|string",
            "location" => "required|string|max:255",
            "cover_image" => "required|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            "start_date" => "required|date",
            "end_date" => "required|date",  
            "start_time" => "required|date",
            "end_time" => "required|date",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_repeat" => "boolean",
            "link" => "nullable|url",
            "event_category_id" => "required|exists:event_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (Event::where('title', $request->title)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Title event tidak boleh sama.',
                'data' => null
            ], 422);
        }

        $event = Event::create([
            "slug" => $request->slug,
            "title" => $request->title,
            "description" => $request->description,
            "location" => $request->location,
            "cover_image" => $request->cover_image,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "start_time" => $request->start_time,
            "end_time" => $request->end_time,
            "status" => $request->status ?? 'upcoming',
            "is_repeat" => $request->is_repeat ?? false,
            "link" => $request->link,
            "user_id" => auth()->guard("api")->user()->id,
            "event_category_id" => $request->event_category_id
        ]);

        return new ApiResource(true, "Successfully created event", $event);
    }

    public function show($id)
    {
        $event = Event::with(['user', 'eventCategory'])->findOrFail($id);

        return new ApiResource(true, "Detail event berdasarkan id", $event);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|alpha_dash|unique:events,slug,".$id."|max:255",
            "title" => "sometimes|string|max:255",
            "description" => "sometimes|string",
            "location" => "sometimes|string|max:255",
            "cover_image" => "sometimes|string",
            "start_date" => "sometimes|date",
            "end_date" => "sometimes|date",
            "start_time" => "sometimes|date",
            "end_time" => "sometimes|date",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_repeat" => "boolean",
            "link" => "nullable|url",
            "user_id" => "sometimes|exists:users,id",
            "event_category_id" => "sometimes|exists:event_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

      if ($request->title && Event::where('title', $request->title)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Title event tidak boleh sama.',
                'data' => null
            ], 422);
        }

        $event = Event::findOrFail($id);

        $event->update([
            "slug" => $request->slug,
            "title" => $request->title ?? $event->title,
            "description" => $request->description ?? $event->description,
            "location" => $request->location ?? $event->location,
            "cover_image" => $request->cover_image ?? $event->cover_image,
            "start_date" => $request->start_date ?? $event->start_date,
            "end_date" => $request->end_date ?? $event->end_date,
            "start_time" => $request->start_time ?? $event->start_time,
            "end_time" => $request->end_time ?? $event->end_time,
            "status" => $request->status ?? $event->status,
            "is_repeat" => $request->is_repeat ?? $event->is_repeat,
            "link" => $request->link ?? $event->link,
            "user_id" => $request->user_id ?? $event->user_id,
            "event_category_id" => $request->event_category_id ?? $event->event_category_id
        ]);

        return new ApiResource(true, "Successfully updated event.", $event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return new ApiResource(true, "Successfully deleted event.", $event);
    }
}