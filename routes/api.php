<?php

use App\Http\Controllers\Api\PlatformApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| JSON API exposing platform resources. Public read models are open; any
| customer/provider scoped data requires a Sanctum bearer token issued by
| POST /api/auth/token.
|
*/

Route::get('/', [PlatformApiController::class, 'index']);

Route::post('/auth/token', [PlatformApiController::class, 'token']);

Route::get('/services', [PlatformApiController::class, 'services']);
Route::get('/providers', [PlatformApiController::class, 'providers']);
Route::get('/providers/{provider}', [PlatformApiController::class, 'providerDetail']);
Route::get('/providers/{provider}/reviews', [PlatformApiController::class, 'providerReviews']);
Route::get('/projects', [PlatformApiController::class, 'projects']);
Route::get('/projects/{project}', [PlatformApiController::class, 'projectDetail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/me', [PlatformApiController::class, 'me']);

    Route::get('/service-requests', [PlatformApiController::class, 'serviceRequests']);
    Route::get('/service-requests/{serviceRequest}', [PlatformApiController::class, 'serviceRequestDetail']);
    Route::post('/service-requests', [PlatformApiController::class, 'storeServiceRequest']);

    Route::get('/quotes', [PlatformApiController::class, 'quotes']);
    Route::get('/appointments', [PlatformApiController::class, 'appointments']);
    Route::get('/reviews', [PlatformApiController::class, 'reviews']);
    Route::get('/inbox', [PlatformApiController::class, 'inbox']);

    Route::post('/projects', [PlatformApiController::class, 'storeProject']);
});