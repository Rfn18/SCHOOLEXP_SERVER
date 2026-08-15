<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\Event;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
     public function __construct(
        protected CloudinaryService $cloudinaryService
    ) {}

    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query
                ->with('category')
                ->latest('created_at')      
                ->paginate(10);

        if ($events->count() === 0) {
            return new ApiResource(true, "List event masih kosong", $events);
        }

        return new ApiResource(true, "List data event", $events);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "slug" => "required|alpha_dash|unique:events,slug|max:255",
            "title" => "required|string|max:255|unique:events,title",
            "description" => "required|string",
            "location" => "required|string|max:255",
            "cover_image" => "required|image|mimes:jpeg,png,jpg,gif|max:5128", 
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "start_time" => "required|date_format:H:i",
            "end_time" => "required|date_format:H:i|after:start_time",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_repeat" => "sometimes|boolean",
            "link" => "nullable|url",
            "event_category_id" => "required|exists:event_categories,id",
        ], [
            'title.unique' => 'Title event tidak boleh sama.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $coverImagePath = $request->file('cover_image')->store('school-exp/events', 'cloudinary');

            $event = Event::create([
                "slug" => $request->slug,
                "title" => $request->title,
                "description" => $request->description,
                "location" => $request->location,
                "cover_image" => $coverImagePath,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date,
                "start_time" => $request->start_time,
                "end_time" => $request->end_time,
                "status" => $request->status ?? 'upcoming',
                "is_repeat" => $request->boolean('is_repeat'),
                "link" => $request->link,
                "user_id" => auth()->guard("api")->id(),
                "event_category_id" => $request->event_category_id,
            ]);

            DB::commit();
            return new ApiResource(true, "Successfully created event", $event);
        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($coverImagePath)) {
                Storage::disk('cloudinary')->delete($coverImagePath);
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => null,
            ], 500);
        }
    }
    public function show($slug)
    {
        $event = Event::with(['user', 'category'])->where('slug', $slug)->firstOrFail();

        return new ApiResource(true, "Detail event berdasarkan slug", $event);
    }

    public function update(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $validator = Validator::make($request->all(), [
            "slug" => "sometimes|alpha_dash|unique:events,slug,".$event->id."|max:255",
            "title" => "sometimes|string|max:255",
            "description" => "sometimes|string",
            "location" => "sometimes|string|max:255",
            "cover_image" => "sometimes|image|mimes:jpeg,png,jpg,gif|max:5128",
            "start_date" => "sometimes|date",
            "end_date" => "sometimes|date",
            "start_time" => "sometimes|date_format:H:i",
            "end_time" => "sometimes|date_format:H:i|after:start_time",
            "status" => "sometimes|in:upcoming,ongoing,completed,cancelled",
            "is_repeat" => "boolean",
            "link" => "nullable|url",
            "user_id" => "sometimes|exists:users,id",
            "event_category_id" => "sometimes|exists:event_categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->title && Event::where('title', $request->title)->where('id', '!=', $event->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Title event tidak boleh sama.',
                'data' => null
            ], 422);
        }

        if ($request->cover_image) {
            $coverImagePath = $request->file('cover_image')->store('cloudinary/events', 'cloudinary');
            $request->merge(['cover_image' => $coverImagePath]);
        }

        if ($event->cover_image && $request->cover_image) {
            Storage::disk('cloudinary')->delete($event->cover_image);
        }

        $event->update([
            "slug" => $request->slug ?? $event->slug,
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

    public function destroy($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $event->delete();

        if ($event->cover_image) {
            Storage::disk('cloudinary')->delete($event->cover_image);
        }

        return new ApiResource(true, "Successfully deleted event.", $event);
    }

    public function search(Request $request)
    {
        $query = Event::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('event_category_id')) {
            $query->where('event_category_id', $request->event_category_id);

        }

        return new ApiResource(true, "Events found.", $query->get());
    }

    public function filterByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $events = Event::whereBetween('start_date', [$request->start_date, $request->end_date])->get();

        return new ApiResource(true, "Events found between specified dates.", $events);
    }

    public function filterByCategory($categoryId)
    {
        $events = Event::where('event_category_id', $categoryId)->get();

        if ($events->isEmpty()) {
            return new ApiResource(true, "No events found for this category.", $events);
        }

        return new ApiResource(true, "Events found for the specified category.", $events);
    }

    public function filterByStatus($status)
    {
        $events = Event::where('status', $status)->get();

        if ($events->isEmpty()) {
            return new ApiResource(true, "No events found with this status.", $events);
        }

        return new ApiResource(true, "Events found with the specified status.", $events);
    }

    public function filterByUser($userId)
    {
        $events = Event::where('user_id', $userId)->get();

        if ($events->isEmpty()) {
            return new ApiResource(true, "No events found for this user.", $events);
        }

        return new ApiResource(true, "Events found for the specified user.", $events);
    }

}
