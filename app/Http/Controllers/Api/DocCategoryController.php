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
            return new ApiResource(true, 'List doc category masih kosong', $docCategories);
        }

        return new ApiResource(true, 'List data doc category', $docCategories);
    }

    public function show($id)
    {
        $docCategory = DocCategories::findOrFail($id);

        return new ApiResource(true, 'Detail doc category berdasarkan id', $docCategory);
    }
}