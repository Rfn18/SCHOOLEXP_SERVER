<?php

use App\Http\Controllers\Api\DocCategoryController;
use App\Http\Controllers\Api\DocGalleries;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::apiResource('roles', RoleController::class);

Route::middleware('auth:api')->group(function () {
     Route::get('/me', [UserAuthController::class, 'me']);
     Route::post('/refresh', [UserAuthController::class, 'refresh']);
     Route::delete('/logout', [UserAuthController::class, 'logout']);
     Route::apiResource('users', UserManagementController::class);
     
     Route::patch('/documentations/reorder', [DocumentationController::class, 'reorder']);
     Route::patch('/users/change-profile-picture', [UserManagementController::class, 'changeProfilePicture']);
     Route::patch('/doc-galleries/reorder', [DocGalleries::class, 'reorder']);
     Route::get('/event-categories', [EventCategoryController::class, 'index']);
     Route::get('/doc-categories', [DocCategoryController::class, 'index']);
});

Route::apiResource('doc-galleries', DocGalleries::class);
Route::apiResource('event-category', EventCategoryController::class);
Route::apiResource('events', EventController::class);
Route::apiResource('doc-category', DocCategoryController::class);
Route::apiResource('documentations', DocumentationController::class);