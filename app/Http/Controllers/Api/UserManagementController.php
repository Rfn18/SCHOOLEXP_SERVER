<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function index()
    {
          $user = User::with('role')->paginate(10);
          return new ApiResource(true, "List user", $user);
    }

    public function store(Request $request)
    {     
          $validator = Validator::make($request->all(), [
               'name' => 'required|string',
               'email' => 'required|email',
               'password' => 'required|string|min:6',
               'role_id' => 'required|exists:roles,id',
               'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
          ]);

          if ($validator->fails()) {
               return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => $validator->errors()
               ], 422);
          }

          $path = null;
          if ($request->profile_picture) {
               $path = Storage::disk('cloudinary')->put("users", $request->file('profile_picture'));
          }

          $user = User::create([
               'name' => $request->name,
               'email' => $request->email,
               'password' => Hash::make($request->password),
               'role_id' => $request->role_id,
               'profile_picture' => $path,
          ]);

          return new ApiResource(true, "User created successfully", $user);
    }

    public function show($id)
    {
          $user = User::with('role')->where($id)->first();

          return new ApiResource(true, "User found", $user);
    }

    public function update(Request $request, User $user)
    {
         $validator = Validator::make($request->all(), [
              'name' => 'required|string',
              'email' => 'required|email',
              'password' => 'required|string|min:6',
              'role_id' => 'required|exists:roles,id',
              'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
         ]);

         if ($validator->fails()) {
              return response()->json([
                   'success' => false,
                   'message' => 'Validation failed',
                   'data' => $validator->errors()
              ], 422);
         }

         $path = $user->profile_picture;
         if ($request->profile_picture) {
              $path = Storage::disk('cloudinary')->put("users", $request->file('profile_picture'));
         }

         $user->update([
              'name' => $request->name,
              'email' => $request->email,
              'password' => $request->password ? Hash::make($request->password) : $user->password,
              'role_id' => $request->role_id,
              'profile_picture' => $path,
         ]);

         return new ApiResource(true, "User updated successfully", $user);
    }

    public function destroy(User $user)
    {
         if($user->profile_picture) {
              Storage::disk('cloudinary')->delete($user->profile_picture);
         }
         $user->delete();
         return new ApiResource(true, "User deleted successfully", $user);
    }

    public function changeProfilePicture(Request $request, User $user)
    {
         $validator = Validator::make($request->all(), [
              'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
         ]);

         if ($validator->fails()) {
              return response()->json([
                   'success' => false,
                   'message' => 'Validation failed',
                   'data' => $validator->errors()
              ], 422);
         }

         $path = null;
         if ($request->profile_picture) {
              $path = Storage::disk('cloudinary')->put("users", $request->file('profile_picture'));
         }

         $user->update([
              'profile_picture' => $path,
         ]);

         return new ApiResource(true, "User profile picture changed successfully", $user);
    }
}
