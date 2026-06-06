<?php

use App\Http\Controllers\Api\DocCategoryController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::apiResource('roles', RoleController::class);

Route::middleware('auth:api')->group(function () {
     Route::get('/me', [UserAuthController::class, 'me']);
     Route::post('/refresh', [UserAuthController::class, 'refresh']);
     Route::delete('/logout', [UserAuthController::class, 'logout']);
});

Route::apiResource('event-category', EventCategoryController::class);
Route::apiResource('event', EventController::class);
Route::apiResource('doc-category', DocCategoryController::class);
Route::apiResource('documentation', DocumentationController::class);