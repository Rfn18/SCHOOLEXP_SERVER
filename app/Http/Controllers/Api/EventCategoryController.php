<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\EventCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
    public function index()
    {
        $eventCategories = EventCategories::paginate(10);

        if ($eventCategories->count() === 0) {
            return new ApiResource(true, 'List masih kosong', $eventCategories);
        }

        return new ApiResource(true, 'List data jenis', $eventCategories);
    }

    public function show($id)
    {
        $eventCategory = EventCategories::findOrFail($id);

        return new ApiResource(true, 'List data menu berdasarkan id.', $eventCategory);
    }
}
