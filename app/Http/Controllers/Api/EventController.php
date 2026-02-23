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
            "poster" => "required|string",
            "date" => "required|date",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_all_day" => "boolean",
            "user_id" => "required|exists:users,id",
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
            "poster" => $request->poster,
            "date" => $request->date,
            "status" => $request->status ?? 'upcoming',
            "is_all_day" => $request->is_all_day ?? false,
            "user_id" => $request->user_id,
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
            "poster" => "sometimes|string",
            "date" => "sometimes|date",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_all_day" => "boolean",
            "user_id" => "sometimes|exists:users,id",
            "event_category_id" => "sometimes|exists:event_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (Event::where('title', $request->title)
            ->where('id', '!=', $id)
            ->exists()) {
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
            "poster" => $request->poster ?? $event->poster,
            "date" => $request->date ?? $event->date,
            "status" => $request->status ?? $event->status,
            "is_all_day" => $request->is_all_day ?? $event->is_all_day,
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