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
            return new ApiResource(true, 'List event masih kosong', $events);
        }

        return new ApiResource(true, 'List data event', $events);
    }

    public function show($slug)
    {
        $event = Event::with(['user', 'category'])->where('slug', $slug)->firstOrFail();

        return new ApiResource(true, 'Detail event berdasarkan slug', $event);
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
