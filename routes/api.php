<?php

use App\Http\Controllers\Api\DocCategoryController;
use App\Http\Controllers\Api\DocGalleries;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('roles', RoleController::class);

Route::get('/auth/email/verify/{id}/{hash}', [VerificationController::class, 'verify']);
Route::post('/auth/email/resend', [VerificationController::class, 'resend'])
    ->middleware('throttle:6,1');

Route::middleware('auth:api')->group(function () {
     Route::get('/me', [AuthController::class, 'me']);
     Route::post('/refresh', [AuthController::class, 'refresh']);
     Route::delete('/logout', [AuthController::class, 'logout']);
     Route::apiResource('users', UserManagementController::class);
     
     Route::patch('/documentations/reorder', [DocumentationController::class, 'reorder']);
     Route::patch('/users/change-profile-picture', [UserManagementController::class, 'changeProfilePicture']);
     Route::patch('/doc-galleries/reorder', [DocGalleries::class, 'reorder']);
     Route::get('/doc-categories', [DocCategoryController::class, 'index']);
});
Route::get('/event-categories', [EventCategoryController::class, 'index']);

Route::get('/documentations/top-by-category', [DocumentationController::class, 'topByCategory']);

Route::get('/doc-galleries/by-event/{slug}', [DocGalleries::class, 'showByEventSlug']);
Route::apiResource('doc-galleries', DocGalleries::class);
Route::apiResource('event-category', EventCategoryController::class);
Route::apiResource('events', EventController::class);
Route::apiResource('doc-category', DocCategoryController::class);

Route::post('/documentations/reorder', [DocumentationController::class, 'reorder']);
Route::post('/documentations/bulk-create', [DocumentationController::class, 'bulkCreate']);
Route::put('/documentations/bulk-update/{documentation}', [DocumentationController::class, 'bulkUpdate']);
Route::delete('/documentations/bulk-delete/{documentation}', [DocumentationController::class, 'bulkDelete']);
Route::get('/documentations/highlight', [DocumentationController::class, 'highlight']);
Route::apiResource('documentations', DocumentationController::class);
Route::post('/feedback', [FeedbackController::class, 'store']);