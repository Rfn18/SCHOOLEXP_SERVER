<?php

use App\Http\Controllers\Api\DocCategoryController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('auth-sanctum');

Route::apiResource('event-category', EventCategoryController::class);
Route::apiResource('event', EventController::class);
Route::apiResource('doc-category', DocCategoryController::class);
Route::apiResource('documentation', DocumentationController::class);