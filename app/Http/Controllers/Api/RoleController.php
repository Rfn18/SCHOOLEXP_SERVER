<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResource;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Roles::paginate(10);
        return new ApiResource(true, 'List Data Role', $roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
        ]);

        $role = Roles::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
        ]);

        return new ApiResource(true, 'Role created successfully', $role);
    }

}
