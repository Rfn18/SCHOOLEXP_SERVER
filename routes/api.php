<?php

use App\Http\Controllers\Admin\DocCategoryController as AdminDocCategoryController;
use App\Http\Controllers\Admin\DocGalleriesController;
use App\Http\Controllers\Admin\DocumentationController as AdminDocumentationController;
use App\Http\Controllers\Admin\EventCategoryController as AdminEventCategoryController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Api\DocCategoryController;
use App\Http\Controllers\Api\DocGalleries;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/auth/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

Route::post('/auth/email/resend', [VerificationController::class, 'resend'])
    ->middleware('throttle:3,1');
Route::apiResource('roles', RoleController::class);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('csrf.cookie');

});
    
Route::middleware(['auth:api', 'verified.session', 'csrf.cookie'])->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('csrf.cookie');
    Route::apiResource('users', UserManagementController::class);
    Route::patch('/users/change-profile-picture', [UserManagementController::class, 'changeProfilePicture']);

    Route::post('/admin/documentations/reorder', [AdminDocumentationController::class, 'reorder']);
    Route::post('/admin/documentations/bulk-create', [AdminDocumentationController::class, 'bulkCreate']);
    Route::put('/admin/documentations/bulk-update/{documentation}', [AdminDocumentationController::class, 'bulkUpdate']);
    Route::delete('/admin/documentations/bulk-delete/{documentation}', [AdminDocumentationController::class, 'bulkDelete']);
    Route::patch('/admin/documentations/{documentation}/highlight', [AdminDocumentationController::class, 'setHighlight']);

    Route::post('/admin/doc-galleries/reorder', [DocGalleriesController::class, 'reorder']);
    Route::apiResource('admin/doc-galleries', DocGalleriesController::class)->only(['store', 'update', 'destroy']);

    Route::apiResource('admin/events', AdminEventController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('admin/event-category', AdminEventCategoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('admin/doc-category', AdminDocCategoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('admin/documentations', AdminDocumentationController::class)->only(['store', 'update', 'destroy']);
});

Route::get('/event-categories', [EventCategoryController::class, 'index']);
Route::get('/documentations/top-by-category', [DocumentationController::class, 'topByCategory']);
Route::get('/doc-galleries/by-event/{slug}', [DocGalleries::class, 'showByEventSlug']);
Route::get('/documentations/highlight', [DocumentationController::class, 'highlight']);

Route::apiResource('doc-galleries', DocGalleries::class)->only(['index', 'show']);
Route::apiResource('event-category', EventCategoryController::class)->only(['index', 'show']);
Route::apiResource('events', EventController::class)->only(['index', 'show']);
Route::apiResource('doc-category', DocCategoryController::class)->only(['index', 'show']);
Route::apiResource('documentations', DocumentationController::class)->only(['index', 'show']);
Route::post('/feedback', [FeedbackController::class, 'store']);