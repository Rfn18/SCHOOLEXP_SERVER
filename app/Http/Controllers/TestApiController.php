<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestApiController extends Controller
{
    public function index()
    {
        return view('testing.api-tester');
    }
}